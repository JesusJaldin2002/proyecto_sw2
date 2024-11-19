<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'lesson_id', 'completion_date', 'status', 'time_spent'];

    // Relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el modelo Lesson
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
