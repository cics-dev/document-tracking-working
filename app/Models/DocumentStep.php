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
        'office_id',
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

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function getActiveUserAttribute()
    {
    // If the step is already processed/approved, lock it to the historical user_id
    if ($this->status !== 'Pending') {
        return $this->user;
    }

    // For pending steps, if the office head has changed, this automatically pulls the new head
    return $this->office?->head ?? $this->user;
    }
}