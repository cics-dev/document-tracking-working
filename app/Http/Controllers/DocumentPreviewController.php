<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class DocumentPreviewController extends Controller
{
    public function preview(Request $request)
    {
        $key = array_key_first($request->all());
        $data = session()->get($key);

        if (!$data) {
            abort(404, 'Preview session expired or not found.');
        }

        $data = $this->normalizePreviewData($data);
        $temporaryFiles = [];

        $generatedPdf = $this->temporaryFile('generated_');
        $temporaryFiles[] = $generatedPdf;

        Pdf::loadView('pdf.document-preview', $data)
            ->setPaper([0, 0, 612.00, 936.00])
            ->save($generatedPdf);

        session()->forget($key);

        // Cleanup the single generated preview file on shutdown
        register_shutdown_function(function () use ($temporaryFiles): void {
            foreach ($temporaryFiles as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        });

        $filename = $data['action'] === 'preview'
            ? 'document-preview.pdf'
            : ($data['documentNumber'] ?? 'Document') . '.pdf';

        return response()->file($generatedPdf, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
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