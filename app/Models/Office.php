<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = ['name', 'abbreviation', 'workflow_key', 'office_type', 'head_id', 'acting_head_id', 'office_logo'];

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function actingHead()
    {
        return $this->belongsTo(User::class, 'acting_head_id');
    }

    public function getWorkflowAssigneeAttribute(): ?User
    {
        return $this->actingHead ?? $this->head;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sentDocuments()
    {
        return $this->hasMany(Document::class, 'from_id');
    }

    public function receivedDocuments()
    {
        return $this->hasMany(Document::class, 'to_id');
    }

    public function steps()
    {
        return $this->hasMany(DocumentStep::class);
    }
}
