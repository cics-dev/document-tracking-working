<?php

namespace App\Services;

use App\Models\ExternalDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ExternalDocumentQueryService
{
    public function listFor(User $user): Builder
    {
        $query = ExternalDocument::query();
        if ($user->hasAccess('view_all_external_documents')) {
            return $query;
        }
        if (! $user->hasAccess('receive_external_documents') && ! $user->hasAccess('send_external_documents')) {
            return $query->whereRaw('1 = 0');
        }

        $officeIds = array_values(array_filter($user->workflowOfficeIds(), fn ($id) =>
            $user->hasAccess('receive_external_documents', $id) || $user->hasAccess('send_external_documents', $id)));

        return $query->whereIn('to_id', $officeIds);
    }

    public function canView(User $user, ExternalDocument $document): bool
    {
        return $this->listFor($user)->whereKey($document->id)->exists();
    }
}
