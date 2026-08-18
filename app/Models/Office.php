<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'abbreviation', 'workflow_key', 'office_type', 'head_id', 'acting_head_id', 'office_logo'];

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id')->withTrashed();
    }

    public function actingHead()
    {
        return $this->belongsTo(User::class, 'acting_head_id')->withTrashed();
    }

    public function getWorkflowAssigneeAttribute(): ?User
    {
        if ($this->actingHead && ! $this->actingHead->trashed()) {
            return $this->actingHead;
        }

        return $this->head && ! $this->head->trashed() ? $this->head : null;
    }

    public function workflowAssigneePosition(): ?string
    {
        if ($this->actingHead && ! $this->actingHead->trashed()) {
            return 'Officer-in-Charge'.($this->name ? ', '.$this->name : '');
        }

        return $this->workflow_assignee?->position;
    }

    public function qualifyPosition(?string $position): ?string
    {
        if (blank($position) || $position === 'N/A' || blank($this->name)) {
            return $position;
        }

        $normalize = fn (string $value) => str($value)->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->squish();
        $positionWords = $normalize($position)->explode(' ')->filter(fn ($word) => strlen($word) > 2);
        $officeName = $normalize($this->name);

        return $positionWords->every(fn ($word) => $officeName->contains($word))
            || $officeName->contains($normalize($position))
            || $normalize($position)->contains($officeName)
                ? $position
                : $position.', '.$this->name;
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
