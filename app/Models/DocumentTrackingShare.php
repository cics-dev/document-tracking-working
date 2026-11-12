<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTrackingShare extends Model
{
    protected $fillable = ['document_id', 'created_by', 'token_hash', 'expires_at', 'revoked_at'];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
