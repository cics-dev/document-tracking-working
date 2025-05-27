<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Fpdi;
use Spatie\Browsershot\Browsershot;

class DocumentPreviewController extends Controller
{    
    public function preview(Request $request)
    {
        $data = $request->all();

        $data['date_sent'] = $data['date_sent'] ?? now();
        $data['attachment'] = $data['attachment'] ?? null;

        $pdfPath = storage_path('app/public/' . $data['attachment']);

        // dd($data['document']);
        if (isset($data['document']) && is_string($data['document'])) {
            $data['document'] = json_decode($data['document'], true);
        }
        if (isset($data['signatories']) && is_string($data['signatories'])) {
            $data['signatories'] = json_decode($data['signatories'], true);
        }
        if (isset($data['cfs']) && is_string($data['cfs'])) {
            $data['cfs'] = json_decode($data['cfs'], true);
        }

        $tempGeneratedPdf = tempnam(sys_get_temp_dir(), 'generated_') . '.pdf';
        $tempMergedPdf = null;

        Pdf::loadView('pdf.document-preview', $data)
            ->setPaper([0, 0, 612.00, 936.00])
            ->save($tempGeneratedPdf);

        $pdfToShow = $tempGeneratedPdf;

        if ($data['attachment'] != null) {
            $tempMergedPdf = tempnam(sys_get_temp_dir(), 'merged_') . '.pdf';

            $this->mergePdfs([
                $tempGeneratedPdf,
                public_path('storage/'.$data['attachment']),
            ], $tempMergedPdf);

            $pdfToShow = $tempMergedPdf;
        }

        if ($data['action'] === 'preview') {
            $response = response()->file($pdfToShow, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="document-preview.pdf"',
            ]);
        } else {
            $response = response()->file($pdfToShow, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . ($data['documentNumber'] ?? 'Document') . '.pdf"',
            ]);
        }

        register_shutdown_function(function () use ($tempGeneratedPdf, $tempMergedPdf) {
            if (file_exists($tempGeneratedPdf)) {
                unlink($tempGeneratedPdf);
            }
            if (file_exists($tempMergedPdf)) {
                unlink($tempMergedPdf);
            }
        });

        return $response;
    }

    function mergePdfs($files, $outputPath)
    {
        $pdf = new Fpdi();

        foreach ($files as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }

        $pdf->Output('F', $outputPath);
    }
}
