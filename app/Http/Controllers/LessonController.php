<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function show($id)
    {
        $lesson = Lesson::findOrFail($id);
        return view('lessons.show', compact('lesson'));
    }

    public function next($id)
{
    // Encuentra la lección actual por su ID
    $lesson = Lesson::findOrFail($id);

    // Actualiza el estado de la lección a "completado"
    $lesson->status = true;
    $lesson->save();

    // Redirige al módulo correspondiente
    $moduleId = $lesson->module->id;
    return redirect()->route('modules.show', $moduleId)
        ->with('success', 'Lección completada exitosamente.');
}

}
