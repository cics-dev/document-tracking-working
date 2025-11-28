<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalDocument extends Model
{
    protected $fillable = [
        'document_number',
        'from',
        'to_id',
        'subject',
        'received_date',
        'file_url',
        'file_type',
        'document_id',
    ];

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
}
