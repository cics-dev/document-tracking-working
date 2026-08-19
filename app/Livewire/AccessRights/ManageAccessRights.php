<?php

namespace App\Livewire\AccessRights;

use App\Models\DocumentType;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleAccessService;
use Livewire\Component;

class ManageAccessRights extends Component
{
    public array $rights = [];

    public array $documentTypes = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_access_rights'), 403);
        $this->loadRoles();
    }

    public function saveRole(int $roleId): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_access_rights'), 403);
        $role = Role::findOrFail($roleId);
        app(RoleAccessService::class)->save(
            $role,
            $this->rights[$roleId] ?? [],
            $this->documentTypes[$roleId] ?? [],
            auth()->user(),
            "rights.$roleId",
        );

        session()->flash('status', "Access rights for {$role->description} updated.");
        $this->loadRoles();
    }

    public function render()
    {
        return view('livewire.access-rights.manage-access-rights', [
            'roles' => Role::orderBy('description')->get(),
            'permissions' => Permission::orderBy('label')->get(),
            'types' => DocumentType::orderBy('name')->get(),
        ])->layout('layouts.app');
    }

    private function loadRoles(): void
    {
        foreach (Role::with('permissions', 'role_document_types')->get() as $role) {
            $selections = app(RoleAccessService::class)->selections($role);
            $this->rights[$role->id] = $selections['rights'];
            $this->documentTypes[$role->id] = $selections['documentTypes'];
        }
    }
}
