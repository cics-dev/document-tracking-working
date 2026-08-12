<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentGenerationRule;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\User;

class DocumentGenerationService
{
    public function availableForInternal(Document $document, User $user)
    {
        if (! $user->hasAccess('send_documents')) return collect();
        return DocumentGenerationRule::with('targetType')->where('source_context', 'internal')
            ->where('source_document_type_id', $document->document_type_id)->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereKey($user->effectiveRoleId()))
            ->get()->filter(fn ($rule) => $this->allowedInternal($rule, $document, $user))->values();
    }

    public function availableForExternal(ExternalDocument $document, User $user)
    {
        if (! $user->hasAccess('send_external_documents')) return collect();
        return DocumentGenerationRule::with('targetType')->where('source_context', 'external')
            ->whereNull('source_document_type_id')->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereKey($user->effectiveRoleId()))
            ->get()->filter(fn ($rule) => $this->allowedExternal($rule, $document, $user))->values();
    }

    public function redirectData(DocumentGenerationRule $rule, Document|ExternalDocument $source, User $user): array
    {
        abort_unless($rule->is_active && $rule->roles()->where('roles.id', $user->effectiveRoleId())->exists(), 403, 'This generation action is not assigned to your role.');
        abort_unless($user->hasAccess($rule->source_context === 'external' ? 'send_external_documents' : 'send_documents'), 403, 'You do not have permission to generate documents in this context.');
        if ($source instanceof Document) {
            abort_unless($this->allowedInternal($rule, $source, $user), 403, 'This generation action is not allowed for this document.');
            $data = [
                'to' => $source->fromOffice?->id, 'from' => $source->toOffice?->id,
                'subject' => 'RE: '.$source->subject, 'original_document_id' => $source->id,
            ];
            if ($source->steps->where('step_type', 'signatory')->isNotEmpty()) {
                $data['cf'] = $source->steps->where('step_type', 'signatory')->pluck('user.office.id')
                    ->push($source->from_id)->push(Office::where('workflow_key', 'records')->value('id'))
                    ->push($user->office_id)->flatten()->filter()->unique()->values()->all();
            }
        } else {
            abort_unless($this->allowedExternal($rule, $source, $user), 403, 'This generation action is not allowed for this external document.');
            $data = ['subject' => 'RE: '.$source->subject, 'external_document_id' => $source->id];
            if ($rule->targetType?->abbreviation === 'ECLR') $data['to'] = $source->from;
        }
        $data['document_type_id'] = $rule->target_document_type_id;
        $data['document_type'] = $rule->targetType->abbreviation;
        return $data;
    }

    private function allowedInternal(DocumentGenerationRule $rule, Document $document, User $user): bool
    {
        if ($rule->source_context !== 'internal' || $rule->source_document_type_id !== $document->document_type_id) return false;
        if ($rule->required_status && $document->status !== $rule->required_status) return false;
        if (! $rule->requires_assigned_office) return true;
        $step = $document->steps->first(fn ($step) => $step->step_type === 'action' && $step->status === 'Pending');
        return $step?->isAssignedTo($user) ?? false;
    }

    private function allowedExternal(DocumentGenerationRule $rule, ExternalDocument $document, User $user): bool
    {
        if ($rule->source_context !== 'external' || $document->document_id) return false;
        return ! $rule->requires_assigned_office || $document->toOffice?->workflow_assignee?->is($user);
    }
}
