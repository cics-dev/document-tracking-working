<?php

namespace App\Services;

use App\Models\DocumentType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoleAccessService
{
    public function selections(Role $role): array
    {
        $role->loadMissing('permissions', 'role_document_types');

        return [
            'rights' => $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'documentTypes' => $role->role_document_types
                ->where('is_allowed', true)
                ->pluck('document_type_id')
                ->map(fn ($id) => (string) $id)
                ->all(),
        ];
    }

    public function save(Role $role, array $rights, array $documentTypes, User $actor, string $errorKey = 'rights', string $requiredOwnPermission = 'manage_access_rights'): void
    {
        $requiredPermissionId = Permission::where('key', $requiredOwnPermission)->value('id');
        if ($role->id === $actor->role_id && $requiredPermissionId && ! in_array((string) $requiredPermissionId, $rights, true)) {
            throw ValidationException::withMessages([
                $errorKey => 'You cannot remove '.Permission::whereKey($requiredPermissionId)->value('label').' from your own role.',
            ]);
        }

        DB::transaction(function () use ($role, $rights, $documentTypes): void {
            $role->permissions()->sync($rights);

            foreach (DocumentType::pluck('id') as $typeId) {
                $role->role_document_types()->updateOrCreate(
                    ['document_type_id' => $typeId],
                    ['is_allowed' => in_array((string) $typeId, $documentTypes, true)]
                );
            }
        });
    }
}
