<?php

namespace Tests\Feature;

use App\Livewire\Documents\ViewExternalDocument;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class ExternalDocumentPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_previewable_attachment_is_not_loaded_and_cannot_trigger_a_download(): void
    {
        [$user, $office] = $this->viewer();
        $document = ExternalDocument::create(['document_number' => 'EXT-1', 'from' => 'Agency', 'to_id' => $office->id, 'subject' => 'ZIP', 'received_date' => now(), 'file_url' => 'attachments/test.zip', 'file_type' => 'zip']);
        $this->actingAs($user);

        Livewire::test(ViewExternalDocument::class, ['id' => $document->id])
            ->assertSet('previewUrl', null)
            ->assertSee('will not be downloaded automatically');
        $this->get(route('documents.external-document-preview', $document))->assertStatus(415);
    }

    public function test_office_attachment_uses_the_inline_pdf_preview_instead_of_its_download_url(): void
    {
        [$user, $office] = $this->viewer();
        $document = ExternalDocument::create(['document_number' => 'EXT-DOCX', 'from' => 'Agency', 'to_id' => $office->id, 'subject' => 'DOCX', 'received_date' => now(), 'file_url' => 'attachments/test.docx', 'file_type' => 'docx']);
        $this->actingAs($user);

        Livewire::test(ViewExternalDocument::class, ['id' => $document->id])
            ->assertSet('previewType', 'pdf')
            ->assertSet('previewUrl', route('documents.external-document-preview', $document));
    }

    public function test_pdf_is_served_inline_from_the_authenticated_preview_route(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('attachments/test.pdf', '%PDF-1.4 test');
        [$user, $office] = $this->viewer();
        $document = ExternalDocument::create(['document_number' => 'EXT-2', 'from' => 'Agency', 'to_id' => $office->id, 'subject' => 'PDF', 'received_date' => now(), 'file_url' => 'attachments/test.pdf', 'file_type' => 'pdf']);

        $this->actingAs($user)->get(route('documents.external-document-preview', $document))
            ->assertOk()->assertHeader('content-disposition', 'inline; filename="test.pdf"');
    }

    public function test_csv_is_rejected_as_an_unsupported_preview_type(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('attachments/test.csv', "Name,Amount\nAlpha,100\nBeta,200\n");
        [$user, $office] = $this->viewer();
        $document = ExternalDocument::create(['document_number' => 'EXT-CSV', 'from' => 'Agency', 'to_id' => $office->id, 'subject' => 'CSV', 'received_date' => now(), 'file_url' => 'attachments/test.csv', 'file_type' => 'csv']);

        $this->actingAs($user)->get(route('documents.external-document-preview', $document))->assertStatus(415);
    }

    public function test_docx_is_converted_and_xlsx_is_rejected(): void
    {
        Storage::fake('public');
        $this->createZip('attachments/test.docx', [
            'word/document.xml' => '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Hello DOCX</w:t></w:r></w:p></w:body></w:document>',
        ]);
        $this->createZip('attachments/test.xlsx', [
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row><c t="inlineStr"><is><t>Hello XLSX</t></is></c></row></sheetData></worksheet>',
        ]);
        [$user, $office] = $this->viewer();
        $this->actingAs($user);

        $docx = ExternalDocument::create(['document_number' => 'EXT-DOCX-2', 'from' => 'Agency', 'to_id' => $office->id, 'subject' => 'DOCX', 'received_date' => now(), 'file_url' => 'attachments/test.docx', 'file_type' => 'docx']);
        $this->get(route('documents.external-document-preview', $docx))->assertOk()->assertHeader('content-type', 'application/pdf');
        $xlsx = ExternalDocument::create(['document_number' => 'EXT-XLSX', 'from' => 'Agency', 'to_id' => $office->id, 'subject' => 'XLSX', 'received_date' => now(), 'file_url' => 'attachments/test.xlsx', 'file_type' => 'xlsx']);
        $this->get(route('documents.external-document-preview', $xlsx))->assertStatus(415);
    }

    private function viewer(): array
    {
        $role = Role::create(['role' => 'external-viewer-'.uniqid(), 'description' => 'External Viewer']);
        $role->permissions()->attach(Permission::where('key', 'receive_external_documents')->firstOrFail());
        $office = Office::create(['name' => 'External Office '.uniqid(), 'abbreviation' => 'EO'.random_int(1, 9999), 'office_type' => 'ADMIN']);
        $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $office->update(['head_id' => $user->id]);
        return [$user, $office];
    }

    private function createZip(string $storagePath, array $entries): void
    {
        $path = Storage::disk('public')->path($storagePath);
        if (! is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
        $zip = new ZipArchive; $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($entries as $name => $contents) $zip->addFromString($name, $contents);
        $zip->close();
    }
}
