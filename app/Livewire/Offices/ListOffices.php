<?php

namespace App\Livewire\Offices;

use App\Models\Office;
use App\Services\ArchivalService;
use Livewire\Component;
use Livewire\WithPagination;

class ListOffices extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public bool $showArchived = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_offices'), 403);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedShowArchived(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'typeFilter');
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['name', 'abbreviation', 'office_type', 'created_at'], true)) {
            return;
        }
        $this->sortDirection = $this->sortBy === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortBy = $column;
        $this->resetPage();
    }

    public function editOffice($id)
    {
        return redirect()->route('offices.edit-office', $id);
    }

    public function deleteOffice($id): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_offices'), 403);
        $this->resetErrorBag('archive');
        app(ArchivalService::class)->archiveOffice(Office::findOrFail($id));
        $this->resetPage();
    }

    public function restoreOffice(int $id): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_offices'), 403);
        app(ArchivalService::class)->restoreOffice(Office::onlyTrashed()->findOrFail($id));
        $this->resetPage();
    }

    public function render()
    {
        $offices = Office::query()->when($this->showArchived, fn ($query) => $query->onlyTrashed())->with('head', 'actingHead')
            ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', "%{$this->search}%")->orWhere('abbreviation', 'like', "%{$this->search}%")))
            ->when($this->typeFilter !== '', fn ($query) => $query->where('office_type', $this->typeFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.offices.list-offices', ['offices' => $offices])->layout('layouts.app');
    }
}
