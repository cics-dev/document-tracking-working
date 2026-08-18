<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Document extends Model
{
    protected $casts = [
        'date_sent' => 'date',
        'is_revision' => 'boolean',
    ];

    protected $fillable = [
        'document_number',
        'from_id',
        'from_name',
        'from_position',
        'to_id',
        'to_name',
        'to_position',
        'document_type_id',
        'thru',
        'subject',
        'content',
        'created_by',
        'status',
        'date_sent',
        'file_url',
        'document_level',
        'to_text',
        'is_revision',
        'original_document_id',
    ];

    protected function viewedAt(): Attribute
    {
        return Attribute::get(function () {
            if (! Auth::check()) {
                return null;
            }

            $log = $this->logs()
                ->where('user_id', Auth::id())
                ->where('action', 'Viewed')
                ->latest('created_at')
                ->first();

            return $log ? $log->created_at : null;
        });
    }

    protected function currentRecipient(): Attribute
    {
        return Attribute::get(function () {
            $pendingStep = $this->relationLoaded('steps')
                ? $this->steps->firstWhere('status', 'Pending')
                : $this->nextPendingStep();

            if ($pendingStep) {
                return [
                    'type' => $pendingStep->step_type,
                    'user' => $pendingStep->active_user,
                    'model' => $pendingStep,
                ];
            }

            return null;
        });
    }

    public function revisions()
    {
        return $this->hasMany(Document::class, 'original_document_id');
    }

    public function revisionRoot(): Document
    {
        return $this->originalRevisedDocument?->revisionRoot() ?? $this;
    }

    public function nextPendingStep(): ?DocumentStep
    {
        return $this->steps()
            ->where('status', 'Pending')
            ->orderBy('sequence')
            ->orderBy('id')
            ->first();
    }

    public function allStepsCompleted(): bool
    {
        return $this->steps()->exists()
            && ! $this->steps()->where('status', 'Pending')->exists();
    }

    public function originalRevisedDocument()
    {
        return $this->belongsTo(Document::class, 'original_document_id');
    }

    public function fromOffice()
    {
        return $this->belongsTo(Office::class, 'from_id')->withTrashed();
    }

    public function toOffice()
    {
        return $this->belongsTo(Office::class, 'to_id')->withTrashed();
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function attachments()
    {
        return $this->hasMany(DocumentAttachment::class);
    }

    public function externalDocuments()
    {
        return $this->hasMany(ExternalDocument::class);
    }

    public function getAllAttachmentsAttribute()
    {
        return collect($this->externalDocuments)->values()->map(function ($doc) {
            $doc->setAttribute('name', $doc->document_number);
            $doc->setAttribute('type', 'external');

            return $doc;
        })->merge(
            collect($this->attachments)->values()->map(function ($doc) {
                $doc->setAttribute('type', 'internal');

                return $doc;
            })
        );
    }

    public function steps()
    {
        return $this->hasMany(DocumentStep::class)->orderBy('sequence');
    }

    public function cfs()
    {
        return $this->hasMany(DocumentCarbonCopy::class);
    }

    public function logs()
    {
        return $this->hasMany(DocumentLog::class);
    }

    public function accessLogs()
    {
        return $this->morphMany(DocumentAccessLog::class, 'documentable');
    }

    protected function isViewedByMe(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                if (isset($attributes['is_viewed_by_me'])) {
                    return (bool) $attributes['is_viewed_by_me'];
                }

                if ($this->relationLoaded('accessLogs')) {
                    return $this->accessLogs->isNotEmpty();
                }

                return false;
            }
        );
    }
}
