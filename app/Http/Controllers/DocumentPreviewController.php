<?php

namespace App\Http\Controllers;

use App\Support\IndorsementHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentPreviewController extends Controller
{
    public function preview(Request $request): BinaryFileResponse
    {
        $key = array_key_first($request->query());
        abort_unless(is_string($key) && $key !== '', 404, 'Preview session expired or not found.');

        $data = session()->pull($key);

        abort_unless(is_array($data), 404, 'Preview session expired or not found.');

        $data = $this->normalizePreviewData($data);
        $generatedPdf = $this->temporaryFile('generated_');

        if (IndorsementHeader::applies($data)) {
            $data = array_merge($data, IndorsementHeader::data($data), ['useIlHeader' => true]);
            // IL's header reports the actual page count, rather than a fixed '1 of 2'.
            $firstPass = Pdf::loadView('pdf.document-preview', $data)->setPaper([0, 0, 612.00, 936.00]);
            $firstPass->render();
            $data['ilPageCount'] = $firstPass->getDomPDF()->getCanvas()->get_page_count();
        }

        Pdf::loadView('pdf.document-preview', $data)
            ->setPaper([0, 0, 612.00, 936.00])
            ->save($generatedPdf);

        register_shutdown_function(function () use ($generatedPdf): void {
            if (is_file($generatedPdf)) {
                @unlink($generatedPdf);
            }
        });

        $filename = $data['action'] === 'preview'
            ? 'document-preview.pdf'
            : ($data['documentNumber'] ?? 'Document').'.pdf';

        return response()->file($generatedPdf, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $filename).'"',
        ]);
    }

    private function normalizePreviewData(array $data): array
    {
        $data['date_sent'] = $data['date_sent'] ?? now();

        foreach (['document', 'signatories', 'attachments', 'cfs'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = json_decode($data[$field], true);
            }
        }

        return $data;
    }

    private function temporaryFile(string $prefix): string
    {
        $path = tempnam(sys_get_temp_dir(), $prefix);

        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary PDF file.');
        }

        return $path;
    }
}
