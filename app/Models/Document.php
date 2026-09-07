<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Collection;

class Document extends Model
{
    protected $fillable = ['document_number', 'from_id', 'to_id', 'document_type_id', 'thru', 'subject', 'content', 'created_by', 'status', 'date_sent', 'file_url', 'document_level', 'to_text',
        'is_revision',
        'original_document_id'
    ];
=======

class Document extends Model
{
    protected $fillable = ['document_number', 'from_id', 'to_id', 'document_type_id', 'thru', 'subject', 'content', 'created_by', 'status', 'date_sent', 'file_url', 'document_level', 'to_text'];
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045

    protected function viewedAt(): Attribute
    {
        return Attribute::get(function () {
            $log = $this->logs()
                ->where('user_id', Auth::id())
                ->where('action', 'viewed')
                ->latest('created_at')
                ->first();

            return $log ? $log->created_at : null;
        });
    }
    
    protected function currentRecipient(): Attribute
    {
        return Attribute::get(function () {
            // 1. Check pending routings first (in their defined order)
            $pendingRouting = $this->routings()
                ->where('status', '!=', 'reviewed')
                ->orderBy('sequence')
                ->first();

            if ($pendingRouting) {
                return [
                    'type' => 'routing',
                    'office' => $pendingRouting->office,
                    'model' => $pendingRouting
                ];
            }

            // 2. If all routings are reviewed, check pending signatories
            $pendingSignatory = $this->signatories()
                ->where('status', '!=', 'approved')
                ->orderBy('sequence')
                ->first();

            if ($pendingSignatory) {
                return [
                    'type' => 'signatory',
                    'user' => $pendingSignatory->user,
                    'model' => $pendingSignatory
                ];
            }

            // 3. If everything is completed
            return null;
        });
    }

<<<<<<< HEAD
    public function revisions()
    {
        return $this->hasMany(Document::class, 'original_document_id');
    }

    public function originalRevisedDocument()
    {
        return $this->belongsTo(Document::class, 'original_document_id');
    }
=======
    // public function scopeReadyForUser($query, $userId)
    // {
    //     return $query->where(function($q) use ($userId) {
    //         // Documents where user is in routings and it's their turn
    //         $q->whereHas('routings', function($routingQuery) use ($userId) {
    //             $routingQuery->where('user_id', $userId)
    //                 ->where('status', 'pending')
    //                 ->whereDoesntHave('document.routings', function($subQuery) {
    //                     $subQuery->where('status', 'pending')
    //                         ->whereColumn('document_routings.created_at', '<', 'document_routings.created_at');
    //                 });
    //         })
    //         ->orWhere(function($q) use ($userId) {
    //             // OR documents where user is a signatory and it's their turn
    //             $q->whereHas('signatories', function($signatoryQuery) use ($userId) {
    //                 $signatoryQuery->where('user_id', $userId)
    //                     ->where('status', 'pending')
    //                     // Check that all previous signatories have approved
    //                     ->where(function($sq) {
    //                         $sq->where('sequence', 1)
    //                             ->orWhereHas('document', function($docQuery) {
    //                                 $docQuery->whereDoesntHave('signatories', function($prevQuery) {
    //                                     $prevQuery->whereColumn('sequence', '<', 'document_signatories.sequence')
    //                                         ->where('status', '!=', 'approved');
    //                                 });
    //                             });
    //                     })
    //                     // Check that all routings are reviewed (if any exist)
    //                     ->whereDoesntHave('document.routings', function($routingQuery) {
    //                         $routingQuery->where('status', '!=', 'reviewed');
    //                     });
    //             });
    //         });
    //     });
    // }
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045

    public function fromOffice()
    {
        return $this->belongsTo(Office::class, 'from_id');
    }

    public function toOffice()
    {
        return $this->belongsTo(Office::class, 'to_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->hasMany(DocumentAttachment::class);
    }

<<<<<<< HEAD
    public function externalDocuments()
    {
        return $this->hasMany(ExternalDocument::class);
    }

    // 👇 Custom accessor that merges both
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

=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    public function signatories()
    {
        return $this->hasMany(DocumentSignatory::class);
    }

    public function routings()
    {
        return $this->hasMany(DocumentRouting::class);
    }

    public function cfs()
    {
        return $this->hasMany(DocumentCarbonCopy::class);
    }

    public function logs()
    {
        return $this->hasMany(DocumentLog::class);
    }
<<<<<<< HEAD


    public function accessLogs()
    {
        return $this->morphMany(DocumentAccessLog::class, 'documentable');
    }

    protected function isViewedByMe(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                // Case 1: You used query->withExists(...) (Scenario 1)
                // The value is already sitting in the raw attributes array.
                if (isset($attributes['is_viewed_by_me'])) {
                    return (bool) $attributes['is_viewed_by_me'];
                }

                // Case 2: You used collection->load(...) (Scenario 2 - Current)
                // The accessLogs relationship is loaded in memory.
                if ($this->relationLoaded('accessLogs')) {
                    return $this->accessLogs->isNotEmpty();
                }

                // Case 3: Fallback (Safety net)
                // If we forgot to load anything, assume unread to prevent N+1 queries
                return false; 
            }
        );
    }
=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
}