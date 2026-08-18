<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentQueryService;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentQueryService $documents) {}

    public function getDocument(string $number): Document
    {
        return $this->documents->findByNumber($number);
    }
}
