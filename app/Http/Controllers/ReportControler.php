<?php

namespace App\Http\Controllers;

use App\Exports\FullProgressReportExport;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReportControler extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::user()->id;

        // Obtener todas las lecciones
        $allLessons = Lesson::all();

        // Lecciones completadas
        $completedLessons = Progress::where('user_id', $userId)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->toArray();

        $completedLessonNames = Lesson::whereIn('id', $completedLessons)
            ->pluck('name')
            ->toArray();

        // Lecciones pendientes
        $pendingLessonNames = $allLessons->whereNotIn('id', $completedLessons)
            ->pluck('name')
            ->toArray();

        // Progreso general
        $totalLessons = $allLessons->count();
        $generalProgress = [
            'completed' => count($completedLessonNames),
            'pending' => count($pendingLessonNames),
            'completedLessons' => $completedLessonNames,
            'pendingLessons' => $pendingLessonNames,
        ];

        // Progreso por módulos
        $modules = Module::all();
        $moduleProgress = [
            'modules' => [],
            'percentages' => [],
        ];
        foreach ($modules as $module) {
            $progressPercentage = Progress::getModuleProgressForUser($module, $userId);
            $moduleProgress['modules'][] = $module->name;
            $moduleProgress['percentages'][] = $progressPercentage;
        }

        // Tiempo dedicado por lección
        $progresses = Progress::where('user_id', $userId)->with('lesson')->get();
        $lessonTimeData = [
            'lessons' => [],
            'times' => [],
        ];
        foreach ($progresses as $progress) {
            $lessonName = $progress->lesson->name ?? 'Lección Desconocida';
            $timeParts = explode(':', $progress->time_spent);
            $timeInMinutes = count($timeParts) === 2
                ? (int)$timeParts[0] + ((int)$timeParts[1] / 60)
                : 0;
            $lessonTimeData['lessons'][] = $lessonName;
            $lessonTimeData['times'][] = $timeInMinutes;
        }

        return view('reports.index', compact('generalProgress', 'moduleProgress', 'lessonTimeData'));
    }



    private function convertTimeToMinutes($time)
    {
        // Validar si el tiempo tiene el formato MM:SS o HH:MM:SS
        if (!$time || (!preg_match('/^\d{2}:\d{2}$/', $time) && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $time))) {
            return 0; // Tiempo inválido, retorna 0 minutos
        }

        $parts = explode(':', $time);

        // Si el formato es MM:SS
        if (count($parts) === 2) {
            [$minutes, $seconds] = $parts;
            return $minutes + ($seconds / 60);
        }

        // Si el formato es HH:MM:SS
        if (count($parts) === 3) {
            [$hours, $minutes, $seconds] = $parts;
            return ($hours * 60) + $minutes + ($seconds / 60);
        }

        return 0; // Si no coincide con ninguno, retorna 0 minutos
    }

    public function exportFullReport()
    {
        $userId = Auth::user()->id;

        $generalProgress = $this->getGeneralProgress($userId);
        $moduleProgress = $this->getModuleProgress($userId);
        $lessonTimeData = $this->getLessonTimeData($userId);

        // Generar gráficos como archivos temporales
        $generalProgressChartPath = $this->generateChartImage('doughnut', [
            'labels' => ['Completado', 'Por Hacer'],
            'datasets' => [
                [
                    'data' => [$generalProgress['completed'], $generalProgress['pending']],
                    'backgroundColor' => ['#4CAF50', '#FF5722'],
                ],
            ],
        ]);

        $moduleProgressChartPath = $this->generateChartImage('bar', [
            'labels' => $moduleProgress['modules'],
            'datasets' => [
                [
                    'label' => 'Progreso (%)',
                    'data' => $moduleProgress['percentages'],
                    'backgroundColor' => '#2196F3',
                ],
            ],
        ]);

        $lessonTimeChartPath = $this->generateChartImage('line', [
            'labels' => $lessonTimeData['lessons'],
            'datasets' => [
                [
                    'label' => 'Tiempo Dedicado (min)',
                    'data' => $lessonTimeData['times'],
                    'backgroundColor' => 'rgba(255, 193, 7, 0.2)',
                    'borderColor' => '#FFC107',
                    'borderWidth' => 2,
                ],
            ],
        ]);

        $charts = [
            $generalProgressChartPath,
            $moduleProgressChartPath,
            $lessonTimeChartPath,
        ];

        $export = Excel::download(
            new FullProgressReportExport(
                $generalProgress,
                $moduleProgress,
                $lessonTimeData,
                [
                    'generalProgressChart' => $generalProgressChartPath,
                    'moduleProgressChart' => $moduleProgressChartPath,
                    'lessonTimeChart' => $lessonTimeChartPath,
                ]
            ),
            'reporte_completo_progreso.xlsx'
        );

        // Limpiar archivos temporales
        foreach ($charts as $chartPath) {
            if (file_exists($chartPath)) {
                unlink($chartPath);
            }
        }

        return $export;
    }


    private function getGeneralProgress($userId)
    {
        $allLessons = Lesson::all();
        $completedLessons = Progress::where('user_id', $userId)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->toArray();

        $completedLessonNames = Lesson::whereIn('id', $completedLessons)
            ->pluck('name')
            ->toArray();

        $pendingLessonNames = $allLessons->whereNotIn('id', $completedLessons)
            ->pluck('name')
            ->toArray();

        return [
            'completed' => count($completedLessonNames),
            'pending' => count($pendingLessonNames),
            'completedLessons' => $completedLessonNames,
            'pendingLessons' => $pendingLessonNames,
        ];
    }

    private function getModuleProgress($userId)
    {
        $modules = Module::all();
        $moduleProgress = [
            'modules' => [],
            'percentages' => [],
        ];

        foreach ($modules as $module) {
            $progressPercentage = Progress::getModuleProgressForUser($module, $userId);
            $moduleProgress['modules'][] = $module->name;
            $moduleProgress['percentages'][] = $progressPercentage;
        }

        return $moduleProgress;
    }

    private function getLessonTimeData($userId)
    {
        $progresses = Progress::where('user_id', $userId)->with('lesson')->get();
        $lessonTimeData = [
            'lessons' => [],
            'times' => [],
        ];

        foreach ($progresses as $progress) {
            $lessonName = $progress->lesson->name ?? 'Lección Desconocida';
            $timeParts = explode(':', $progress->time_spent);
            $timeInMinutes = count($timeParts) === 2
                ? (int)$timeParts[0] + ((int)$timeParts[1] / 60)
                : 0;

            $lessonTimeData['lessons'][] = $lessonName;
            $lessonTimeData['times'][] = $timeInMinutes;
        }

        return $lessonTimeData;
    }


    private function generateChartImage($type, $data)
    {
        $chartConfig = [
            'type' => $type,
            'data' => $data,
            'options' => [
                'plugins' => [
                    'legend' => ['position' => 'bottom'],
                ],
            ],
        ];

        $chartUrl = 'https://quickchart.io/chart';
        $response = Http::post($chartUrl, ['chart' => json_encode($chartConfig)]);

        if ($response->successful()) {
            $imageContent = $response->body();

            // Guardar imagen temporalmente para verificarla
            $tempPath = storage_path('app/public/test_chart.png');
            file_put_contents($tempPath, $imageContent);

            if (!@imagecreatefromstring($imageContent)) {
                Log::error('La imagen generada no es válida.');
                throw new \Exception('El gráfico generado no es una imagen válida.');
            }

            return $imageContent;
        } else {
            Log::error('Error al solicitar el gráfico a QuickChart: ' . $response->body());
            throw new \Exception('No se pudo generar el gráfico desde QuickChart.');
        }
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
