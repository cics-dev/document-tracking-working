<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    // public function index($user)
    // {
    //     if ($user->office->id == 18) return DocumentType::whereIn('id', [1, 2, 3, 4, 5])->get();
    //     else if ($user->office->office_type == 'ADMIN') return DocumentType::whereIn('id', [1, 2, 3, 5])->get();
    //     else if ($user->office->office_type == 'ACAD') return DocumentType::whereIn('id', [1, 3, 5, 6])->get();
    //     return DocumentType::all();
    // }

    public function index($user)
    {
        // Get the IDs of allowed document types for the user's role
        $allowedIds = \DB::table('role_document_types')
            ->where('role_id', $user->role_id)
            ->where('is_allowed', true)
            ->pluck('document_type_id');

        // Return only those document types
        return DocumentType::whereIn('id', $allowedIds)->get();
    }
}
