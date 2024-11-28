<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'progresses';

    protected $fillable = ['completion_date', 'status', 'time_spent'];

    // Relación con el modelo Module

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function getProgressPercentageAttribute()
    {
        $module = $this->module; // Obtener el módulo relacionado
        $totalLessons = $module->lessons()->count(); // Total de lecciones en el módulo
        $completedLessons = $module->lessons()->where('status', true)->count(); // Lecciones completadas

        if ($totalLessons === 0) {
            return 0; // Evitar división por 0
        }

        return round(($completedLessons / $totalLessons) * 100);
    }
}
