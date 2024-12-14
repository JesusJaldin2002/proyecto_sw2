<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'progresses';

    protected $fillable = ['lesson_id', 'user_id', 'completion_at', 'status', 'time_spent'];

    /**
     * Relación con las lecciones.
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Relación con los usuarios.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el porcentaje de progreso del módulo para un usuario.
     */
    public static function getModuleProgressForUser($module, $userId)
    {
        $totalLessons = $module->lessons->count();

        // Contar lecciones completadas por el usuario dentro del módulo
        $completedLessons = $module->lessons()
            ->whereHas('progresses', function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('status', 'completed');
            })
            ->count();

        if ($totalLessons === 0) {
            return 0; // Evitar división por cero
        }

        return round(($completedLessons / $totalLessons) * 100);
    }
}
