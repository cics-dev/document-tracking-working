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

    public function getActiveUserAttribute(): ?User
    {
        // Completed steps keep the historical user who acted. Pending steps follow
        // the office's current OIC first, then its designated head.
        if ($this->status !== 'Pending') {
            return $this->user;
        }

        return $this->office?->workflow_assignee ?? $this->user;
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->status === 'Pending'
            && ($this->office?->workflow_assignee?->is($user) ?? $this->user?->is($user));
    }
}
