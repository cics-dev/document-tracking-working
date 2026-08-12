<?php

namespace App\Livewire\Offices;

use App\Models\Office;
use Livewire\Component;
use Livewire\WithPagination;

class ListOffices extends Component
{
    use WithPagination;

    public $name;

    public $abbreviation;

    public $office_type;

    public $head_id;

    public $editMode = false;

    public $officeId;

    public $content = '';

    public string $search = '';

    public string $typeFilter = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    protected $rules = [
        'name' => 'required|string|max:255',
        'abbreviation' => 'required|string|max:50|unique:offices,abbreviation',
        'office_type' => 'required|in:ACAD,ADMIN',
        'head_id' => 'nullable|exists:users,id',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
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

    public function deleteOffice($id)
    {
        Office::findOrFail($id)->delete();
        $this->resetPage();
    }

    public function render()
    {
        $offices = Office::query()->with('head', 'actingHead')
            ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', "%{$this->search}%")->orWhere('abbreviation', 'like', "%{$this->search}%")))
            ->when($this->typeFilter !== '', fn ($query) => $query->where('office_type', $this->typeFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.offices.list-offices', ['offices' => $offices])->layout('layouts.app');
    }
}
