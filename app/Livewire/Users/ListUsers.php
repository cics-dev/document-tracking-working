<?php

namespace App\Livewire\Users;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Services\ArchivalService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ListUsers extends Component
{
    use WithPagination;

    public string $search = '';

    public string $officeFilter = '';

    public string $roleFilter = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public bool $showArchived = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_users'), 403);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'officeFilter', 'roleFilter'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'officeFilter', 'roleFilter');
        $this->resetPage();
    }

    public function updatedShowArchived(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['name', 'email', 'position', 'created_at'], true)) {
            return;
        }
        $this->sortDirection = $this->sortBy === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortBy = $column;
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()->when($this->showArchived, fn (Builder $query) => $query->onlyTrashed())->with('profile', 'office')
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('position', 'like', "%{$this->search}%")))
            ->when($this->officeFilter !== '', fn (Builder $query) => $query->where('office_id', $this->officeFilter))
            ->when($this->roleFilter !== '', fn (Builder $query) => $query->where('role_id', $this->roleFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.users.list-users', [
            'users' => $users,
            'offices' => Office::orderBy('name')->get(['id', 'name', 'abbreviation']),
            'roles' => Role::orderBy('role')->get(['id', 'role']),
        ])->layout('layouts.app');
    }

    public function editUser($id)
    {
        return redirect()->route('users.edit-user', $id);
    }

    public function deleteUser($id): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_users'), 403);
        abort_if((int) $id === auth()->id(), 422, 'You cannot deactivate your own account here.');
        app(ArchivalService::class)->archiveUser(User::findOrFail($id));
        $this->resetPage();
    }

    public function restoreUser(int $id): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_users'), 403);
        app(ArchivalService::class)->restoreUser(User::onlyTrashed()->findOrFail($id));
        $this->resetPage();
    }
}
