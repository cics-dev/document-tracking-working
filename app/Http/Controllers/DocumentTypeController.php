<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DocumentTypeController extends Controller
{
    public function index(User $user)
    {
        $allowedIds = DB::table('role_document_types')
            ->where('role_id', $user->effectiveRoleId())
            ->where('is_allowed', true)
            ->pluck('document_type_id');

        return DocumentType::where(function ($query) use ($allowedIds) {
            $query->whereIn('id', $allowedIds)
                ->orWhere('is_publicly_creatable', true);
        })->get();
    }
}
