<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;

final class DocumentPosition
{
    public static function display(?string $snapshot, ?User $user): ?string
    {
        if (blank($snapshot)) {
            return $user?->documentPosition();
        }

        // Translate legacy role keys while retaining explicitly saved titles and OIC wording.
        return Role::where('role', $snapshot)->value('description') ?: $snapshot;
    }
}
