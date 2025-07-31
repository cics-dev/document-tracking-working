<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index($user)
    {
        if ($user->office->id == 18) return DocumentType::whereIn('id', [1, 2, 3, 4, 5])->get();
        else if ($user->office->office_type == 'ADMIN') return DocumentType::whereIn('id', [1, 2, 3, 5])->get();
        else if ($user->office->office_type == 'ACAD') return DocumentType::whereIn('id', [1, 3, 5, 6])->get();
        return DocumentType::all();
    }
}
