<?php

namespace App\Livewire\Roles;

use App\Models\DocumentType;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleAccessService;
use Illuminate\Support\Facades\DB;
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

    public array $rights = [];

    public array $documentTypes = [];

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
        $record = Role::with('permissions', 'role_document_types')->findOrFail($id);
        $this->roleId = $record->id;
        $this->role = $record->role;
        $this->description = $record->description;
        $selections = app(RoleAccessService::class)->selections($record);
        $this->rights = $selections['rights'];
        $this->documentTypes = $selections['documentTypes'];
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $data = $this->validate([
            'role' => ['required', 'string', 'max:100', Rule::unique('roles', 'role')->ignore($this->roleId)],
            'description' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data): void {
            $record = Role::updateOrCreate(['id' => $this->roleId], $data);
            app(RoleAccessService::class)->save($record, $this->rights, $this->documentTypes, auth()->user(), 'rights', 'manage_roles');
        });
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
        $this->reset('roleId', 'role', 'description', 'rights', 'documentTypes');
        $this->resetValidation();
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_roles'), 403);
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

        return view('livewire.roles.manage-roles', [
            'roles' => $roles,
            'permissions' => Permission::orderBy('label')->get(),
            'types' => DocumentType::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
