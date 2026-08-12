<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentGenerationRule extends Model
{
    protected $attributes = ['requires_assigned_office' => true, 'is_active' => true];
    protected $fillable = ['source_context', 'source_document_type_id', 'target_document_type_id', 'button_label', 'required_status', 'requires_assigned_office', 'is_active'];
    protected $casts = ['requires_assigned_office' => 'boolean', 'is_active' => 'boolean'];
    public function sourceType() { return $this->belongsTo(DocumentType::class, 'source_document_type_id'); }
    public function targetType() { return $this->belongsTo(DocumentType::class, 'target_document_type_id'); }
    public function roles() { return $this->belongsToMany(Role::class, 'document_generation_rule_role'); }
}
