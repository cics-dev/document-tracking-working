<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentWorkflowService
{
    public function __construct(private TwoFactorAuthentication $twoFactor) {}

    /**
     * Process the next pending workflow step for the authenticated assignee.
     *
     * @return array{document: Document, step: DocumentStep, completed: bool, description: string}
     */
    public function approve(Document $document, User $actor, ?string $remarks = null, ?string $code = null, ?int $stepId = null): array
    {
        return $this->process($document, $actor, $remarks, false, $code, $stepId);
    }

    /**
     * Reject or return the next pending workflow step for the authenticated assignee.
     *
     * @return array{document: Document, step: DocumentStep, completed: bool, description: string}
     */
    public function reject(Document $document, User $actor, ?string $remarks, ?string $code = null, ?int $stepId = null): array
    {
        return $this->process($document, $actor, $remarks, true, $code, $stepId);
    }

    private function process(Document $document, User $actor, ?string $remarks, bool $isRejection, ?string $code, ?int $stepId): array
    {
        $remarks = is_string($remarks) ? trim($remarks) : $remarks;
        $remarks = $remarks === '' ? null : $remarks;

        $this->twoFactor->throttle($actor->id, $code);

        $result = DB::transaction(function () use ($document, $actor, $remarks, $isRejection, $code, $stepId) {
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

            if ($stepId !== null && $step->id !== $stepId) {
                throw ValidationException::withMessages(['document' => 'The current signing step changed. Reload the document and try again.']);
            }

            if ($step->step_type === 'signatory') {
                $signer = User::query()->lockForUpdate()->findOrFail($actor->id);

                if ($signer->require_two_factor_for_signing) {
                    $this->twoFactor->verify($signer, $code);
                }
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

        app(DocumentEmailService::class)->processed($result);

        return $result;
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
            ['routing', true] => 'returned the document',
            ['action', false] => 'completed action on the document',
            ['action', true] => 'rejected action on the document',
            ['signatory', true] => 'rejected the document',
            default => 'signed the document',
        };

        if ($isRejection && $remarks !== null) {
            $action .= ' with remarks';
        }

        return trim(sprintf('%s %s%s', $actor->office?->name ?? $actor->name, $action, $remarks !== null ? ': '.$remarks : ''));
    }
}
