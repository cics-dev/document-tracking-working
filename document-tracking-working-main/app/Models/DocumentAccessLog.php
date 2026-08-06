<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAccessLog extends Model
{
    protected $fillable = [
        'documentable_id',   // The ID of the doc
        'documentable_type', // The class name (App\Models\Document, etc.)
        'user_id',
        'action'
    ];

    /**
     * Get the parent documentable model (Document or ExternalDocument).
     */
    public function documentable()
    {
        return $this->morphTo();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
