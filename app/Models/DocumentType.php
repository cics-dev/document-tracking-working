<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = [
        'name', 'abbreviation', 'recipient_mode', 'recipient_label', 'recipient_office_key',
        'document_level', 'number_prefix', 'show_thru', 'show_carbon_copy',
        'requires_signatories', 'is_publicly_creatable', 'content_template',
    ];

    protected $casts = [
        'show_thru' => 'boolean', 'show_carbon_copy' => 'boolean',
        'requires_signatories' => 'boolean', 'is_publicly_creatable' => 'boolean',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function flowStages()
    {
        return $this->hasMany(DocumentFlowStage::class)->orderBy('sequence');
    }
}
