<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $attributes = [
        'chip_color' => '#dbeafe',
    ];

    protected $fillable = [
        'name', 'abbreviation', 'chip_color', 'recipient_mode', 'recipient_label', 'recipient_office_id',
        'document_level', 'number_prefix', 'show_thru', 'show_carbon_copy', 'allow_attachments',
        'requires_signatories', 'is_publicly_creatable', 'content_template', 'print_layout',
        'sender_signature_policy', 'approver_display_mode', 'allow_oic_signature',
    ];

    protected $casts = [
        'show_thru' => 'boolean', 'show_carbon_copy' => 'boolean', 'allow_attachments' => 'boolean',
        'requires_signatories' => 'boolean', 'is_publicly_creatable' => 'boolean',
        'allow_oic_signature' => 'boolean',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function flowStages()
    {
        return $this->hasMany(DocumentFlowStage::class)->orderBy('sequence');
    }

    public function recipientOffice()
    {
        return $this->belongsTo(Office::class, 'recipient_office_id');
    }
}
