<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['name', 'description', 'status'];

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Obtener el progreso del módulo para un usuario.
     */
    public function getProgressForUser($userId)
    {
        return Progress::getModuleProgressForUser($this, $userId);
    }
}
