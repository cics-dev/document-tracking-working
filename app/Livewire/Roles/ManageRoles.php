<?php

namespace App\Livewire\Roles;

use App\Models\Role;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageRoles extends Component
{
    use WithPagination;

    public ?int $roleId = null;
    public string $role = '';
    public string $description = '';
    public string $search = '';

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $this->authorizeAccess();
        $record = Role::findOrFail($id);
        $this->roleId = $record->id;
        $this->role = $record->role;
        $this->description = $record->description;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $data = $this->validate([
            'role' => ['required', 'string', 'max:100', Rule::unique('roles', 'role')->ignore($this->roleId)],
            'description' => ['required', 'string', 'max:255'],
        ]);

        Role::updateOrCreate(['id' => $this->roleId], $data);
        session()->flash('status', $this->roleId ? 'Role updated.' : 'Role created.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->authorizeAccess();
        $record = Role::withCount('users')->findOrFail($id);

        if ($record->users_count > 0) {
            $this->addError('delete', 'This role is assigned to users and cannot be deleted. Reassign those users first.');
            return;
        }

        $record->permissions()->detach();
        $record->role_document_types()->delete();
        $record->delete();
        session()->flash('status', 'Role deleted.');
        $this->resetForm();
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset('roleId', 'role', 'description');
        $this->resetValidation();
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_access_rights'), 403);
    }

    public function render()
    {
        $roles = Role::query()
            ->withCount('users')
            ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('role', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->orderBy('description')
            ->paginate(10);

        return view('livewire.roles.manage-roles', compact('roles'))->layout('layouts.app');
    }
}
