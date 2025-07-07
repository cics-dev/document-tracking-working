<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index($office_type)
    {
        if ($office_type != 'ADMIN') return DocumentType::whereIn('id', [1, 3])->get();
        return DocumentType::all();
    }
}
