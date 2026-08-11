<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'step_type',
        'step_label',
        'sequence',
        'status',
        'comments',
        'viewed_at',
        'processed_at',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'viewed_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}