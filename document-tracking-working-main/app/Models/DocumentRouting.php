<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRouting extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'comments',
        'status',
        'viewed_at',
        'reviewed_at',
        'returned_at',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
