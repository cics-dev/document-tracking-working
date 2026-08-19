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
        $documents = app(DocumentQueryService::class);
        $canViewAll = $user->hasAccess('view_all_documents');
        $canReceive = $user->hasAccess('receive_documents');
        $canSend = $user->hasAccess('send_documents');
        $external = ExternalDocument::query();

        if (! $canViewAll) {
            $external->whereIn('to_id', $user->workflowOfficeIds());
        }

        $counts = [
            'all' => $canViewAll
                ? Document::whereDoesntHave('accessLogs', $viewed)->count() : 0,
            'received' => $canReceive
                ? $documents->listFor($user, 'received')->whereDoesntHave('accessLogs', $viewed)->count() : 0,
            'sent' => $canSend
                ? $documents->listFor($user, 'Sent')->whereDoesntHave('accessLogs', $viewed)->count() : 0,
            'external' => ($user->hasAccess('receive_external_documents') || $user->hasAccess('send_external_documents'))
                ? $external->whereDoesntHave('accessLogs', $viewed)->count() : 0,
        ];
        $counts['total'] = ($canViewAll
            ? $counts['all']
            : $this->unreadInternalTotal($documents, $user, $viewed, $canReceive, $canSend)
        ) + $counts['external'];

        return $counts;
    }

    private function unreadInternalTotal(
        DocumentQueryService $documents,
        User $user,
        callable $viewed,
        bool $canReceive,
        bool $canSend,
    ): int {
        if (! $canReceive && ! $canSend) {
            return 0;
        }

        return Document::query()
            ->where(function ($query) use ($documents, $user, $canReceive, $canSend) {
                if ($canReceive) {
                    $query->whereIn('documents.id', $documents->listFor($user, 'received')->select('documents.id'));
                }

                if ($canSend) {
                    $method = $canReceive ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('documents.id', $documents->listFor($user, 'Sent')->select('documents.id'));
                }
            })
            ->whereDoesntHave('accessLogs', $viewed)
            ->count();
    }
}
