<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentFlowStage extends Model
{
    protected $fillable = ['document_type_id', 'office_id', 'stage_type', 'label', 'description', 'sequence', 'is_required', 'is_selectable', 'workflow_condition_id', 'condition_operator', 'condition_value'];

    protected $casts = ['is_required' => 'boolean', 'is_selectable' => 'boolean', 'sequence' => 'integer'];

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class)->withTrashed();
    }

    public function workflowCondition()
    {
        return $this->belongsTo(WorkflowCondition::class);
    }
}
