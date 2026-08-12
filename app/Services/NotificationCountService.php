<?php

namespace App\Services;

use App\Models\Document;
use App\Models\ExternalDocument;
use App\Models\User;

class NotificationCountService
{
    public function for(User $user): array
    {
        $viewed = fn ($query) => $query->where('user_id', $user->id)->where('action', 'Viewed');
        $external = ExternalDocument::query();
        $isPrivileged = $user->position === 'Staff'
            || $user->position === 'University President'
            || $user->office?->name === 'Records Section';
        if (! $isPrivileged) $external->where('to_id', $user->office_id);

        $counts = [
            'all' => $user->hasAccess('view_all_documents')
                ? Document::whereDoesntHave('accessLogs', $viewed)->count() : 0,
            'received' => $user->hasAccess('receive_documents')
                ? app(DocumentQueryService::class)->receivedBy(Document::query(), $user)->whereDoesntHave('accessLogs', $viewed)->count() : 0,
            'external' => ($user->hasAccess('receive_external_documents') || $user->hasAccess('send_external_documents'))
                ? $external->whereDoesntHave('accessLogs', $viewed)->count() : 0,
        ];
        $counts['total'] = ($user->hasAccess('view_all_documents') ? $counts['all'] : $counts['received']) + $counts['external'];
        return $counts;
    }
}
