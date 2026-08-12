<?php

namespace App\Livewire\Notifications;

use App\Services\NotificationCountService;
use Livewire\Component;

class SidebarNotifications extends Component
{
    public int $unreadAll = 0;
    public int $unreadReceived = 0;
    public int $unreadExternal = 0;
    public int $unreadTotal = 0;
    public bool $showToast = false;

    public function mount(): void { $this->applyCounts(app(NotificationCountService::class)->for(auth()->user())); }

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

    public function dismissToast(): void { $this->showToast = false; }

    private function applyCounts(array $counts): void
    {
        $this->unreadAll = $counts['all']; $this->unreadReceived = $counts['received'];
        $this->unreadExternal = $counts['external']; $this->unreadTotal = $counts['total'];
    }

    public function render() { return view('livewire.notifications.sidebar-notifications'); }
}
