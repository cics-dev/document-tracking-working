<?php

namespace App\Http\Controllers;

use App\Models\DocumentAttachment;
use App\Services\AttachmentPreviewService;
use App\Services\DocumentQueryService;

class DocumentAttachmentPreviewController extends Controller
{
    public function __invoke(DocumentAttachment $documentAttachment, AttachmentPreviewService $preview)
    {
        abort_unless(app(DocumentQueryService::class)->canView(auth()->user(), $documentAttachment->document->document_number), 403);
        abort_unless($documentAttachment->is_upload, 404);
        return $preview->response($documentAttachment->file_url);
    }
}
