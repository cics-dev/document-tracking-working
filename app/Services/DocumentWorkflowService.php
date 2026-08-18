<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentWorkflowService
{
    /**
     * Process the next pending workflow step for the authenticated assignee.
     *
     * @return array{document: Document, step: DocumentStep, completed: bool, description: string}
     */
    public function approve(Document $document, User $actor, ?string $remarks = null): array
    {
        return $this->process($document, $actor, $remarks, false);
    }

    /**
     * Reject or return the next pending workflow step for the authenticated assignee.
     *
     * @return array{document: Document, step: DocumentStep, completed: bool, description: string}
     */
    public function reject(Document $document, User $actor, ?string $remarks): array
    {
        return $this->process($document, $actor, $remarks, true);
    }

    private function process(Document $document, User $actor, ?string $remarks, bool $isRejection): array
    {
        return DB::transaction(function () use ($document, $actor, $remarks, $isRejection) {
            $document = Document::query()->lockForUpdate()->findOrFail($document->id);

            if (! in_array($document->status, ['Sent', 'In Process'], true)) {
                throw ValidationException::withMessages(['document' => 'This document is no longer awaiting action.']);
            }

            $step = DocumentStep::query()
                ->where('document_id', $document->id)
                ->where('status', 'Pending')
                ->orderBy('sequence')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            $step?->loadMissing('office.actingHead', 'office.head');
            if (! $step || ! $step->isAssignedTo($actor)) {
                throw ValidationException::withMessages(['document' => 'It is not your turn to act on this document.']);
            }

            $status = $this->stepStatus($step, $isRejection);
            $description = $this->description($step, $actor, $remarks, $isRejection);

            $step->update([
                'user_id' => $actor->id,
                'signature_path' => $actor->signature ?? $step->signature_path,
                'signed_for' => ($step->assigned_user_id ?? $step->user_id) !== $actor->id,
                'processed_at' => now(),
                'comments' => $remarks,
                'status' => $status,
            ]);

            $completed = false;
            if ($isRejection) {
                $document->update(['status' => $step->step_type === 'routing' ? 'Returned' : 'Rejected']);
            } else {
                $completed = ! DocumentStep::query()
                    ->where('document_id', $document->id)
                    ->whereIn('step_type', ['routing', 'signatory'])
                    ->where(function ($query) {
                        $query->whereNull('processed_at')
                            ->orWhereNotIn('status', ['Approved', 'Reviewed']);
                    })
                    ->exists();

                $document->update(['status' => $completed ? 'Approved' : 'In Process']);
            }

            $document->logs()->create([
                'user_id' => $actor->id,
                'action' => $isRejection ? $status : 'signed',
                'description' => $description,
            ]);

            return compact('document', 'step', 'completed', 'description');
        });
    }

    private function stepStatus(DocumentStep $step, bool $isRejection): string
    {
        return match ([$step->step_type, $isRejection]) {
            ['routing', false] => 'Reviewed',
            ['routing', true] => 'Returned',
            ['action', true] => 'Rejected',
            default => $isRejection ? 'Rejected' : 'Approved',
        };
    }

    private function description(DocumentStep $step, User $actor, ?string $remarks, bool $isRejection): string
    {
        $action = match ([$step->step_type, $isRejection]) {
            ['routing', false] => 'reviewed the document',
            ['routing', true] => 'returned the document with remarks',
            ['action', false] => 'completed action on the document',
            ['action', true] => 'rejected action on the document with remarks',
            ['signatory', true] => 'rejected the document with remarks',
            default => 'signed the document',
        };

        return trim(sprintf('%s %s%s', $actor->office?->name ?? $actor->name, $action, $remarks ? ': '.$remarks : ''));
    }
}
