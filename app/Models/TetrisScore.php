<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TetrisScore extends Model
{
    protected $fillable = ['user_id', 'score'];

    protected $casts = [
        'score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}