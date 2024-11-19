<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['title', 'message', 'sent_date', 'type'];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
