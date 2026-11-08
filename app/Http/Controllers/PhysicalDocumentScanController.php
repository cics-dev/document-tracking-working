<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentQueryService;
use Illuminate\Support\Facades\Storage;

class PhysicalDocumentScanController extends Controller
{
    public function __invoke(Document $document, DocumentQueryService $documents)
    {
        abort_unless($document->is_physical, 404);
        abort_unless($documents->canView(auth()->user(), $document->document_number), 403);

        $disk = Storage::disk('local');
        abort_unless($document->file_url && $disk->exists($document->file_url), 404);

        return $disk->response($document->file_url, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
