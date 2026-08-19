<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DocumentQueryService
{
    public function canView(User $user, string $number): bool
    {
        if ($user->hasAccess('view_all_documents')) {
            return Document::where('document_number', $number)->exists();
        }

        return ($user->hasAccess('send_documents') && $this->listFor($user, 'Sent')->where('document_number', $number)->exists())
            || ($user->hasAccess('receive_documents') && $this->receivedBy(Document::query(), $user)->where('document_number', $number)->exists());
    }

    public function listFor(User $user, string $mode): Builder
    {
        $query = Document::query()->with([
            'documentType',
            'fromOffice.head',
            'toOffice.head',
            'steps.user.office.head',
            'steps.office.head',
            'steps.office.actingHead',
            'cfs.user.office.head',
        ]);

        $officeIds = $user->workflowOfficeIds();

        return match ($mode) {
            'all' => $query,
            'Sent' => $query->where(function (Builder $query) use ($user, $officeIds) {
                $query->whereIn('from_id', $officeIds)
                    ->orWhere('created_by', $user->id);
            }),
            'received' => $this->receivedBy($query, $user),
            default => abort(404),
        };
    }

    public function receivedBy(Builder $query, User $user): Builder
    {
        $officeIds = $user->workflowOfficeIds();

        return $query
            ->where('status', '!=', 'Draft')
            ->where(function (Builder $query) use ($user, $officeIds) {
                $query->where(function (Builder $direct) use ($officeIds) {
                    // A direct recipient may immediately see documents that have no
                    // workflow. When workflow steps exist, visibility must respect
                    // their order instead of bypassing them through documents.to_id.
                    $direct->whereIn('to_id', $officeIds)
                        ->whereDoesntHave('steps');
                })
                    ->orWhereHas('steps', fn (Builder $steps) => $steps
                        ->where(function (Builder $steps) use ($user, $officeIds) {
                            $steps->where('user_id', $user->id)
                                ->orWhereIn('office_id', $officeIds);
                        })
                        ->where(function (Builder $steps) {
                            $steps->where('status', '!=', 'Pending')
                                ->orWhere(function (Builder $steps) {
                                    $steps->where('status', 'Pending')
                                        ->whereNotExists(function ($previousSteps) {
                                            $previousSteps->selectRaw('1')
                                                ->from('document_steps as previous_steps')
                                                ->whereColumn('previous_steps.document_id', 'document_steps.document_id')
                                                ->where('previous_steps.status', 'Pending')
                                                ->where(function ($previousSteps) {
                                                    $previousSteps
                                                        ->whereColumn('previous_steps.sequence', '<', 'document_steps.sequence')
                                                        ->orWhere(function ($previousSteps) {
                                                            $previousSteps
                                                                ->whereColumn('previous_steps.sequence', '=', 'document_steps.sequence')
                                                                ->whereColumn('previous_steps.id', '<', 'document_steps.id');
                                                        });
                                                });
                                        });
                                });
                        }))
                    ->orWhereHas('cfs', fn (Builder $copies) => $copies->where('user_id', $user->id));
            });
    }

    public function findByNumber(string $number): Document
    {
        return Document::with([
            'fromOffice.head',
            'toOffice.head',
            'documentType',
            'attachments.attachmentDocument',
            'externalDocuments',
            'steps.user.office.head',
            'steps.office.head',
            'steps.office.actingHead',
            'cfs.user.office.head',
            'logs',
        ])->where('document_number', $number)->firstOrFail();
    }
}
