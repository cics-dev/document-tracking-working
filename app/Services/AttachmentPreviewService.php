<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use ZipArchive;

class AttachmentPreviewService
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
    public const OFFICE_EXTENSIONS = ['doc', 'docx'];

    public function response(string $storagePath)
    {
        $extension = strtolower(pathinfo($storagePath, PATHINFO_EXTENSION));
        abort_unless(in_array($extension, self::ALLOWED_EXTENSIONS, true), 415, 'This file type cannot be previewed.');
        abort_unless(Storage::disk('public')->exists($storagePath), 404);
        $path = Storage::disk('public')->path($storagePath);
        $mime = Storage::disk('public')->mimeType($storagePath) ?: 'application/octet-stream';

        if ($extension === 'docx') {
            $path = $this->convertWithBuiltInRenderer($path, $extension);
            $mime = 'application/pdf';
        } elseif (in_array($extension, self::OFFICE_EXTENSIONS, true)) {
            $path = $this->convertToPdf($path);
            $mime = 'application/pdf';
        }

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.pathinfo($storagePath, PATHINFO_FILENAME).($mime === 'application/pdf' ? '.pdf' : '.'.$extension).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function previewType(?string $storagePath): ?string
    {
        $extension = strtolower(pathinfo($storagePath ?? '', PATHINFO_EXTENSION));
        if ($extension === 'pdf' || in_array($extension, self::OFFICE_EXTENSIONS, true)) return 'pdf';
        if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) return 'image';
        return null;
    }

    private function convertToPdf(string $source): string
    {
        $directory = sys_get_temp_dir().'/attachment_preview_'.bin2hex(random_bytes(8));
        $profile = $directory.'/profile';
        abort_unless(mkdir($directory, 0700, true) && mkdir($profile, 0700, true), 500, 'Unable to prepare the attachment preview.');
        $copy = $directory.'/'.basename($source);
        abort_unless(copy($source, $copy), 500, 'Unable to prepare the attachment preview.');

        $profileUrl = 'file://'.str_replace('%2F', '/', rawurlencode($profile));
        $process = new Process(['soffice', '-env:UserInstallation='.$profileUrl, '--headless', '--convert-to', 'pdf', '--outdir', $directory, $copy]);
        $process->setTimeout(90);
        $process->run();
        $pdf = $directory.'/'.pathinfo($copy, PATHINFO_FILENAME).'.pdf';
        abort_unless($process->isSuccessful() && is_file($pdf), 422, 'The attachment could not be converted for preview. Please verify that the file is valid and not password-protected.');

        register_shutdown_function(function () use ($directory): void { $this->removeDirectory($directory); });
        return $pdf;
    }

    private function convertWithBuiltInRenderer(string $source, string $extension): string
    {
        $html = match ($extension) {
            'csv' => $this->csvHtml($source),
            'xlsx' => $this->xlsxHtml($source),
            'docx' => $this->docxHtml($source),
        };
        $pdf = tempnam(sys_get_temp_dir(), 'attachment_pdf_');
        abort_unless($pdf !== false, 500, 'Unable to prepare the attachment preview.');
        Pdf::loadHTML('<html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans;font-size:10px}table{border-collapse:collapse;width:100%}td,th{border:1px solid #999;padding:4px;vertical-align:top}p{margin:0 0 8px}</style></head><body>'.$html.'</body></html>')
            ->setPaper('a4', 'portrait')->save($pdf);
        register_shutdown_function(fn () => is_file($pdf) && @unlink($pdf));
        return $pdf;
    }

    private function csvHtml(string $source): string
    {
        $handle = fopen($source, 'rb');
        abort_unless($handle, 422, 'The CSV file could not be read.');
        $rows = '';
        while (($row = fgetcsv($handle)) !== false) {
            $rows .= '<tr>'.collect($row)->map(fn ($cell) => '<td>'.e((string) $cell).'</td>')->implode('').'</tr>';
        }
        fclose($handle);
        return '<table>'.$rows.'</table>';
    }

    private function docxHtml(string $source): string
    {
        $xml = $this->zipEntry($source, 'word/document.xml');
        $dom = new DOMDocument; abort_unless(@$dom->loadXML($xml), 422, 'The DOCX file is invalid.');
        $xpath = new DOMXPath($dom); $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $html = '';
        foreach ($xpath->query('//w:body/w:p | //w:body/w:tbl') as $node) {
            if ($node->localName === 'p') $html .= '<p>'.e($this->nodeText($xpath, $node, './/w:t')).'</p>';
            else {
                $html .= '<table>';
                foreach ($xpath->query('.//w:tr', $node) as $row) {
                    $html .= '<tr>';
                    foreach ($xpath->query('./w:tc', $row) as $cell) $html .= '<td>'.e($this->nodeText($xpath, $cell, './/w:t')).'</td>';
                    $html .= '</tr>';
                }
                $html .= '</table><br>';
            }
        }
        return $html ?: '<p>(Empty document)</p>';
    }

    private function xlsxHtml(string $source): string
    {
        $shared = [];
        if (($xml = $this->zipEntry($source, 'xl/sharedStrings.xml', false)) !== null) {
            $dom = new DOMDocument; @$dom->loadXML($xml); $xpath = new DOMXPath($dom); $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($xpath->query('//x:si') as $item) $shared[] = $this->nodeText($xpath, $item, './/x:t');
        }
        $xml = $this->zipEntry($source, 'xl/worksheets/sheet1.xml');
        $dom = new DOMDocument; abort_unless(@$dom->loadXML($xml), 422, 'The XLSX file is invalid.');
        $xpath = new DOMXPath($dom); $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $html = '<table>';
        foreach ($xpath->query('//x:sheetData/x:row') as $row) {
            $html .= '<tr>';
            foreach ($xpath->query('./x:c', $row) as $cell) {
                $value = $xpath->evaluate('string(./x:v)', $cell);
                if ($cell->getAttribute('t') === 's') $value = $shared[(int) $value] ?? '';
                elseif ($cell->getAttribute('t') === 'inlineStr') $value = $this->nodeText($xpath, $cell, './/x:t');
                $html .= '<td>'.e($value).'</td>';
            }
            $html .= '</tr>';
        }
        return $html.'</table>';
    }

    private function zipEntry(string $source, string $entry, bool $required = true): ?string
    {
        $zip = new ZipArchive;
        abort_unless($zip->open($source) === true, 422, 'The office file is invalid.');
        $contents = $zip->getFromName($entry); $zip->close();
        if ($required) abort_unless($contents !== false, 422, 'The office file does not contain previewable content.');
        return $contents === false ? null : $contents;
    }

    private function nodeText(DOMXPath $xpath, \DOMNode $node, string $query): string
    {
        return collect(iterator_to_array($xpath->query($query, $node)))->map(fn ($item) => $item->textContent)->implode('');
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) return;
        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $item) {
            $path = $directory.'/'.$item;
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }
        @rmdir($directory);
    }
}
