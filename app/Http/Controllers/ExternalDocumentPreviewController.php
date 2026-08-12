<?php

namespace App\Http\Controllers;

use App\Models\ExternalDocument;
use App\Services\AttachmentPreviewService;

class ExternalDocumentPreviewController extends Controller
{
    public function __invoke(ExternalDocument $externalDocument, AttachmentPreviewService $preview)
    {
        $user = auth()->user();
        abort_unless($user?->hasAccess('receive_external_documents') || $user?->hasAccess('send_external_documents'), 403);

        return $preview->response($externalDocument->file_url);
    }
}
