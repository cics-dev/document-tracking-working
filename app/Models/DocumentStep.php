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
        'assigned_user_id',
        'office_id',
        'step_type',
        'step_label',
        'signatory_name',
        'signatory_position',
        'signature_path',
        'signed_for',
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
        'signed_for' => 'boolean',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class)->withTrashed();
    }

    public function getActiveUserAttribute(): ?User
    {
        // Completed steps keep the historical user who acted. Pending steps follow
        // the office's current OIC first, then its designated head.
        if ($this->status !== 'Pending') {
            return $this->user;
        }

        $assignee = $this->office?->workflow_assignee ?? $this->user;

        return $assignee && ! $assignee->trashed() ? $assignee : null;
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->status === 'Pending'
            && $this->active_user?->is($user);
    }
}
