<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function show($id)
    {
        $lesson = Lesson::findOrFail($id);
        return view('lessons.show', compact('lesson'));
    }

    public function next($id, Request $request)
{
    $lesson = Lesson::findOrFail($id);
    $userId = Auth::user()->id;

    // Registrar en lesson_user
    $lesson->users()->syncWithoutDetaching([$userId]);

    // Leer el tiempo en formato HH:MM:SS enviado desde el cliente
    $formattedTime = $request->query('time', '00:00:00');

    // Registrar en progresses
    Progress::updateOrCreate(
        [
            'lesson_id' => $lesson->id,
            'user_id' => $userId,
        ],
        [
            'completion_at' => now(),
            'status' => 'completed',
            'time_spent' => $formattedTime, // Guardar el tiempo en formato HH:MM:SS
        ]
    );

    // Redirigir a la próxima lección
    $nextLesson = Lesson::where('module_id', $lesson->module_id)
        ->where('id', '>', $lesson->id)
        ->orderBy('id')
        ->first();

    if ($nextLesson) {
        return redirect()->route('lessons.show', $nextLesson->id)
            ->with('success', '¡Lección completada! Redirigiendo a la siguiente.');
    }

    // Si no hay más lecciones, redirigir al módulo
    return redirect()->route('modules.show', $lesson->module_id)
        ->with('success', '¡Lección completada! Has terminado este módulo.');
}

}
