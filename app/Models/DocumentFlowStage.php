<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentFlowStage extends Model
{
    protected $fillable = ['document_type_id', 'office_id', 'stage_type', 'label', 'description', 'sequence', 'is_required', 'is_selectable', 'workflow_condition_id', 'condition_operator', 'condition_value', 'sender_role_id', 'sender_role_operator', 'selection_group'];

    protected $casts = ['is_required' => 'boolean', 'is_selectable' => 'boolean', 'sequence' => 'integer'];

    public function senderRole()
    {
        return $this->belongsTo(Role::class, 'sender_role_id');
    }

    public function matchesSenderRole(?Office $office): bool
    {
        if (! $this->sender_role_id) {
            return true;
        }
        $roleId = $office?->head?->role_id;
        if (! $roleId) {
            return false;
        }

        return $this->sender_role_operator === 'not_equals'
            ? (int) $roleId !== (int) $this->sender_role_id
            : (int) $roleId === (int) $this->sender_role_id;
    }

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
