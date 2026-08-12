<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentQueryService;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentQueryService $documents) {}

    public function index(string $mode)
    {
        return $this->documents->listFor(Auth::user(), $mode)->get();
    }

    public function show(int $id)
    {
        return Document::with(['fromOffice', 'toOffice', 'documentType', 'steps'])->findOrFail($id);
    }

    public function getDocument(string $number): Document
    {
        return $this->documents->findByNumber($number);
    }
}
