<?php

namespace App\Http\Controllers;

use App\Models\ExternalDocument;
use App\Services\AttachmentPreviewService;
use App\Services\ExternalDocumentQueryService;

class ExternalDocumentPreviewController extends Controller
{
    public function __invoke(ExternalDocument $externalDocument, AttachmentPreviewService $preview)
    {
        $user = auth()->user();
        abort_unless($user && app(ExternalDocumentQueryService::class)->canView($user, $externalDocument), 403);

        return $preview->response($externalDocument->file_url);
    }
}
