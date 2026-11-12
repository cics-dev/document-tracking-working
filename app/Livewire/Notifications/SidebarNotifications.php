<?php

namespace App\Livewire\Notifications;

use App\Services\NotificationCountService;
use Livewire\Component;
use Livewire\Attributes\Locked;

class SidebarNotifications extends Component
{
    #[Locked]
    public string $activeSection = '';

    public int $unreadAll = 0;

    public int $unreadReceived = 0;

    public int $unreadSent = 0;

    public int $unreadExternal = 0;

    public int $unreadTotal = 0;

    public bool $showToast = false;

    public function mount(): void
    {
        $path = request()->path();
        $this->activeSection = match (true) {
            $path === 'documents/all' => 'all',
            $path === 'documents/received' => 'received',
            $path === 'documents/Sent',
            request()->is('documents/edit-draft/*', 'documents/create-revision/*', 'documents/archive-physical') => 'Sent',
            request()->is('documents/create') => 'write',
            request()->is('documents/list-external-documents', 'documents/receive-external-document', 'documents/view-external-document/*') => 'external',
            request()->is('documents/view/*', 'documents/track/*') => $this->documentListSection(),
            default => '',
        };
        $this->applyCounts(app(NotificationCountService::class)->for(auth()->user()));
    }

    public function refreshNotifications(): void
    {
        $previous = $this->unreadTotal;
        $counts = app(NotificationCountService::class)->for(auth()->user());
        $this->applyCounts($counts);
        if ($this->unreadTotal > $previous) {
            $increase = $this->unreadTotal - $previous;
            $this->showToast = true;
            $this->dispatch('new-document-notification', increase: $increase, total: $this->unreadTotal);
        }
    }

    public function dismissToast(): void
    {
        $this->showToast = false;
    }

    private function documentListSection(): string
    {
        $sections = ['all' => 'view_all_documents', 'Sent' => 'send_documents', 'received' => 'receive_documents'];
        $source = request()->query('list');
        if (is_string($source) && isset($sections[$source]) && auth()->user()->hasAccess($sections[$source])) {
            return $source;
        }

        foreach ($sections as $section => $permission) {
            if (auth()->user()->hasAccess($permission)) {
                return $section;
            }
        }

        return '';
    }

    private function applyCounts(array $counts): void
    {
        $this->unreadAll = $counts['all'];
        $this->unreadReceived = $counts['received'];
        $this->unreadSent = $counts['sent'];
        $this->unreadExternal = $counts['external'];
        $this->unreadTotal = $counts['total'];
    }

    public function render()
    {
        return view('livewire.notifications.sidebar-notifications');
    }
}
