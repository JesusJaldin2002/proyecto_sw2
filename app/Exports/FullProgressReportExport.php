<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class FullProgressReportExport implements FromArray, WithDrawings
{
    protected $generalProgress;
    protected $moduleProgress;
    protected $lessonTimeData;
    protected $charts;

    public function __construct($generalProgress, $moduleProgress, $lessonTimeData, $charts)
    {
        $this->generalProgress = $generalProgress;
        $this->moduleProgress = $moduleProgress;
        $this->lessonTimeData = $lessonTimeData;
        $this->charts = $charts;
    }

    public function array(): array
    {
        $data = [];

        // Progreso General
        $data[] = ['Progreso General'];
        $data[] = ['Estado', 'Cantidad'];
        $data[] = ['Completado', $this->generalProgress['completed']];
        $data[] = ['Por Hacer', $this->generalProgress['pending']];
        $data[] = [''];
        $data[] = ['Lecciones Completadas'];
        foreach ($this->generalProgress['completedLessons'] as $lesson) {
            $data[] = [$lesson];
        }
        $data[] = [''];
        $data[] = ['Lecciones Pendientes'];
        foreach ($this->generalProgress['pendingLessons'] as $lesson) {
            $data[] = [$lesson];
        }
        $data[] = [''];

        // Progreso por Módulos
        $data[] = ['Progreso por Módulos'];
        $data[] = ['Módulo', '% Progreso'];
        foreach ($this->moduleProgress['modules'] as $index => $module) {
            $data[] = [$module, $this->moduleProgress['percentages'][$index]];
        }
        $data[] = [''];

        // Tiempo Dedicado por Lección
        $data[] = ['Tiempo Dedicado por Lección'];
        $data[] = ['Lección', 'Tiempo (min)'];
        foreach ($this->lessonTimeData['lessons'] as $index => $lesson) {
            $data[] = [$lesson, $this->lessonTimeData['times'][$index]];
        }

        return $data;
    }

    public function drawings()
    {
        $drawings = [];
        $tempDir = sys_get_temp_dir();

        try {
            // Progreso General Gráfico
            $generalChartPath = $tempDir . '/general_progress_chart.png';
            file_put_contents($generalChartPath, $this->charts['generalProgressChart']);
            if (file_exists($generalChartPath)) {
                $drawing1 = new Drawing();
                $drawing1->setName('Progreso General');
                $drawing1->setDescription('Progreso General');
                $drawing1->setPath($generalChartPath);
                $drawing1->setHeight(300);
                $drawing1->setCoordinates('E3'); // Ajustado para empezar en la columna E
                $drawings[] = $drawing1;
            } else {
                Log::error("Archivo no encontrado: {$generalChartPath}");
                throw new \Exception("Archivo no encontrado: {$generalChartPath}");
            }

            // Progreso por Módulos Gráfico
            $moduleChartPath = $tempDir . '/module_progress_chart.png';
            file_put_contents($moduleChartPath, $this->charts['moduleProgressChart']);
            if (file_exists($moduleChartPath)) {
                $drawing2 = new Drawing();
                $drawing2->setName('Progreso por Módulos');
                $drawing2->setDescription('Progreso por Módulos');
                $drawing2->setPath($moduleChartPath);
                $drawing2->setHeight(300);
                $drawing2->setCoordinates('E25'); // Ajustado para colocar en la columna E
                $drawings[] = $drawing2;
            } else {
                Log::error("Archivo no encontrado: {$moduleChartPath}");
                throw new \Exception("Archivo no encontrado: {$moduleChartPath}");
            }

            // Tiempo por Lección Gráfico
            $lessonChartPath = $tempDir . '/lesson_time_chart.png';
            file_put_contents($lessonChartPath, $this->charts['lessonTimeChart']);
            if (file_exists($lessonChartPath)) {
                $drawing3 = new Drawing();
                $drawing3->setName('Tiempo por Lección');
                $drawing3->setDescription('Tiempo por Lección');
                $drawing3->setPath($lessonChartPath);
                $drawing3->setHeight(300);
                $drawing3->setCoordinates('E47'); // Ajustado para colocar en la columna E
                $drawings[] = $drawing3;
            } else {
                Log::error("Archivo no encontrado: {$lessonChartPath}");
                throw new \Exception("Archivo no encontrado: {$lessonChartPath}");
            }
        } catch (\Exception $e) {
            Log::error('Error al generar los gráficos: ' . $e->getMessage());
        }

        return $drawings;
    }
}
