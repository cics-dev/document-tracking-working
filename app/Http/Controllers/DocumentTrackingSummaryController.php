<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentQueryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class DocumentTrackingSummaryController extends Controller
{
    public function __invoke(Document $document, DocumentQueryService $documents): Response
    {
        abort_unless(filled($document->document_number) && $documents->canView(auth()->user(), $document->document_number), 403);
        $document->load(['documentType', 'fromOffice', 'toOffice', 'steps.office']);
        $pdf = Pdf::loadView('pdf.tracking-summary', [
            'document' => $document,
            'steps' => $document->steps->sortBy([['sequence', 'asc'], ['id', 'asc']]),
            'logs' => $document->logs()->orderByDesc('created_at')->orderByDesc('id')->limit(10)->get(),
            'printedAt' => now('Asia/Manila'),
        ])->setPaper('a4', 'portrait')->setOption('isRemoteEnabled', false)->setOption('isPhpEnabled', false);
        $pdf->render();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $font = $pdf->getDomPDF()->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $canvas->page_text(460, 808, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0.4, 0.4, 0.4]);
        $filename = 'tracking-summary-'.preg_replace('/[^A-Za-z0-9._-]+/', '-', $document->document_number).'.pdf';

        return $pdf->stream($filename)->withHeaders(['X-Content-Type-Options' => 'nosniff', 'X-Robots-Tag' => 'noindex, noarchive']);
    }
}
