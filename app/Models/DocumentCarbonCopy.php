<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCarbonCopy extends Model
{
    protected $fillable = ['document_id', 'user_id', 'viewed_at'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}