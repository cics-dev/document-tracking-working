<?php

namespace App\Livewire\AccessRights;

use App\Models\DocumentType;
use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;

class ManageAccessRights extends Component
{
    public array $rights = [];
    public array $documentTypes = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_access_rights'), 403);
        foreach (Role::with('permissions', 'role_document_types')->get() as $role) {
            $this->rights[$role->id] = $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
            $this->documentTypes[$role->id] = $role->role_document_types->where('is_allowed', true)->pluck('document_type_id')->map(fn ($id) => (string) $id)->all();
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasAccess('manage_access_rights'), 403);
        $manageAccessId = Permission::where('key', 'manage_access_rights')->value('id');
        if ($manageAccessId && ! in_array((string) $manageAccessId, $this->rights[auth()->user()->role_id] ?? [], true)) {
            $this->addError('rights', 'You cannot remove Manage Access Rights from your own role.');
            return;
        }
        foreach (Role::all() as $role) {
            $role->permissions()->sync($this->rights[$role->id] ?? []);
            foreach (DocumentType::pluck('id') as $typeId) {
                $role->role_document_types()->updateOrCreate(
                    ['document_type_id' => $typeId],
                    ['is_allowed' => in_array((string) $typeId, $this->documentTypes[$role->id] ?? [], true)]
                );
            }
        }
        session()->flash('status', 'Access rights updated.');
    }

    public function render()
    {
        return view('livewire.access-rights.manage-access-rights', [
            'roles' => Role::orderBy('description')->get(),
            'permissions' => Permission::orderBy('label')->get(),
            'types' => DocumentType::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
