<?php

namespace App\Services;

use App\Models\DocumentFlowStage;
use App\Models\DocumentStep;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArchivalService
{
    public function archiveUser(User $user): void
    {
        $hasPendingWork = DocumentStep::where('status', 'Pending')
            ->where(fn ($query) => $query
                ->where('user_id', $user->id)
                ->orWhere('assigned_user_id', $user->id))
            ->exists();

        if ($hasPendingWork) {
            throw ValidationException::withMessages([
                'archive' => 'This user cannot be deactivated while assigned to pending document steps.',
            ]);
        }

        DB::transaction(function () use ($user): void {
            Office::where('head_id', $user->id)->update(['head_id' => null]);
            Office::where('acting_head_id', $user->id)->update(['acting_head_id' => null]);
            $user->delete();
        });
    }

    public function restoreUser(User $user): void
    {
        $user->restore();
    }

    public function archiveOffice(Office $office): void
    {
        $blockers = collect([
            'active users' => $office->users()->exists(),
            'document flow stages' => DocumentFlowStage::where('office_id', $office->id)->exists(),
            'fixed-recipient document types' => DocumentType::where('recipient_office_id', $office->id)->exists(),
            'pending document steps' => DocumentStep::where('office_id', $office->id)->where('status', 'Pending')->exists(),
        ])->filter()->keys();

        if ($blockers->isNotEmpty()) {
            throw ValidationException::withMessages([
                'archive' => 'This office cannot be deactivated while it has '.$blockers->join(', ', ' or ').'.',
            ]);
        }

        $office->delete();
    }

    public function restoreOffice(Office $office): void
    {
        $office->restore();
    }
}
