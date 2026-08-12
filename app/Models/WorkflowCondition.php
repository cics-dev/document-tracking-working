<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowCondition extends Model
{
    protected $fillable = ['key', 'label', 'input_type', 'options', 'is_active'];
    protected $casts = ['options' => 'array', 'is_active' => 'boolean'];
    public function stages() { return $this->hasMany(DocumentFlowStage::class); }
}
