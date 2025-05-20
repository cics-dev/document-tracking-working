<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentPreviewController extends Controller
{
    public function preview(Request $request)
    {
        $data = $request->all();

        $data['date'] = $data['date_sent'] ?? now();

        // Ensure signatories is an array (not string)
        if (isset($data['signatories']) && is_string($data['signatories'])) {
            $data['signatories'] = json_decode($data['signatories'], true);
        }
        if (isset($data['cfs']) && is_string($data['cfs'])) {
            $data['cfs'] = json_decode($data['cfs'], true);
        }

        $pdf = Pdf::loadView('pdf.document-preview', $data)->setPaper([0, 0, 612.00, 936.00]);

        // return $pdf->stream('document-preview.pdf');
        // return response()->streamDownload(function () use ($pdf) {
        //     echo $pdf->stream();
        // }, ($data['action'] == 'preview'?'Draft':$data['documentNumber']).'.pdf');

        if ($data['action'] === 'preview') {
            // Stream inline in browser
            return $pdf->stream('document-preview.pdf');
        } else {
            // Force download
            return $pdf->stream(($data['documentNumber'] ?? 'Document') . '.pdf');
        }
    }
}
