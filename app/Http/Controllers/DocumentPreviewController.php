<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\User;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Fpdi;

class DocumentPreviewController extends Controller
{    
    public function preview(Request $request)
    {
        $key = array_key_first($request->all());
        $data = session()->get($key);

        // dd($data);

        if (!$data) {
            abort(404, 'Preview session expired or not found.');
        }

        $data['date_sent'] = $data['date_sent'] ?? now();

        // dd($data['document']);
        if (isset($data['document']) && is_string($data['document'])) {
            $data['document'] = json_decode($data['document'], true);
        }
        if (isset($data['signatories']) && is_string($data['signatories'])) {
            $data['signatories'] = json_decode($data['signatories'], true);
        }
        if (isset($data['attachments']) && is_string($data['attachments'])) {
            $data['attachments'] = json_decode($data['attachments'], true);
        }
        if (isset($data['cfs']) && is_string($data['cfs'])) {
            $data['cfs'] = json_decode($data['cfs'], true);
        }


        $tempGeneratedPdf = tempnam(sys_get_temp_dir(), 'generated_') . '.pdf';
        $tempMergedPdf = null;

        Pdf::loadView('pdf.document-preview', $data)
            ->setPaper([0, 0, 612.00, 936.00])
            ->save($tempGeneratedPdf);

        $filesToMerge = [$tempGeneratedPdf];

        $pdfToShow = $tempGeneratedPdf;

        if (count($filesToMerge) > 1) {
            $tempMergedPdf = tempnam(sys_get_temp_dir(), 'merged_') . '.pdf';
            $this->mergePdfs($filesToMerge, $tempMergedPdf);
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

        session()->forget($key);

        register_shutdown_function(function () use ($tempGeneratedPdf, $tempMergedPdf) {
            if (file_exists($tempGeneratedPdf)) {
                unlink($tempGeneratedPdf);
            }
            if ($tempMergedPdf && file_exists($tempMergedPdf)) {
                unlink($tempMergedPdf);
            }
        });


        return $response;
    }

    function processAttachment($attachment, &$filesToMerge)
    {
        if (empty($attachment['file_url'])) {
            return;
        }

        $path = public_path('storage/' . $attachment['file_url']);
        if (!file_exists($path)) {
            return;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['pdf'])) {
            $filesToMerge[] = $path;
        } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $imagePdfPath = tempnam(sys_get_temp_dir(), 'img_') . '.pdf';

            \Pdf::loadView('pdf.image-wrapper', [
                'imagePath' => $path
            ])->setPaper([0, 0, 612.00, 936.00])
                ->save($imagePdfPath);

            $filesToMerge[] = $imagePdfPath;
        } elseif (in_array($extension, ['docx'])) {
            // Future: Convert DOCX → PDF
            // $docxPdfPath = tempnam(sys_get_temp_dir(), 'docx_') . '.pdf';
            // $this->convertDocxToPdf($path, $docxPdfPath);
            // $filesToMerge[] = $docxPdfPath;
        }
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
