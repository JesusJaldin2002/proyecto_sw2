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

    public function progress()
    {
        return $this->hasOne(Progress::class);
    }
}
