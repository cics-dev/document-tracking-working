<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentStep;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use App\Services\DocumentQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_received_list_keeps_processed_steps_and_only_shows_ready_pending_steps(): void
    {
        $type = DocumentType::create(['name' => 'Workflow Letter', 'abbreviation' => 'WL']);
        $firstOffice = Office::create(['name' => 'First Office', 'abbreviation' => 'FO', 'office_type' => 'ADMIN']);
        $secondOffice = Office::create(['name' => 'Second Office', 'abbreviation' => 'SO', 'office_type' => 'ADMIN']);
        $firstUser = User::factory()->create(['office_id' => $firstOffice->id]);
        $secondUser = User::factory()->create(['office_id' => $secondOffice->id]);
        $firstOffice->update(['head_id' => $firstUser->id]);
        $secondOffice->update(['head_id' => $secondUser->id]);

        $document = Document::create([
            'document_number' => 'FO-WL-1-2026',
            'from_id' => $firstOffice->id,
            'to_id' => null,
            'document_type_id' => $type->id,
            'subject' => 'Sequenced workflow',
            'content' => 'Test content',
            'created_by' => $firstUser->id,
            'status' => 'In Process',
        ]);

        DocumentStep::create([
            'document_id' => $document->id,
            'user_id' => $firstUser->id,
            'office_id' => $firstOffice->id,
            'step_type' => 'routing',
            'step_label' => 'First review',
            'sequence' => 1,
            'status' => 'Reviewed',
            'processed_at' => now(),
        ]);
        DocumentStep::create([
            'document_id' => $document->id,
            'user_id' => $secondUser->id,
            'office_id' => $secondOffice->id,
            'step_type' => 'signatory',
            'step_label' => 'Second review',
            'sequence' => 2,
        ]);

        $service = app(DocumentQueryService::class);

        $this->assertTrue($service->receivedBy(Document::query(), $firstUser)->whereKey($document)->exists());
        $this->assertTrue($service->receivedBy(Document::query(), $secondUser)->whereKey($document)->exists());
    }

    public function test_pending_step_stays_hidden_until_all_previous_steps_are_processed(): void
    {
        $type = DocumentType::create(['name' => 'Workflow Letter', 'abbreviation' => 'WL']);
        $firstOffice = Office::create(['name' => 'First Office', 'abbreviation' => 'FO', 'office_type' => 'ADMIN']);
        $secondOffice = Office::create(['name' => 'Second Office', 'abbreviation' => 'SO', 'office_type' => 'ADMIN']);
        $firstUser = User::factory()->create(['office_id' => $firstOffice->id]);
        $secondUser = User::factory()->create(['office_id' => $secondOffice->id]);
        $document = Document::create([
            'document_number' => 'FO-WL-2-2026', 'from_id' => $firstOffice->id, 'to_id' => null,
            'document_type_id' => $type->id, 'subject' => 'Blocked workflow', 'content' => 'Test content',
            'created_by' => $firstUser->id, 'status' => 'Sent',
        ]);
        DocumentStep::create(['document_id' => $document->id, 'user_id' => $firstUser->id, 'office_id' => $firstOffice->id, 'step_type' => 'routing', 'step_label' => 'First review', 'sequence' => 1]);
        DocumentStep::create(['document_id' => $document->id, 'user_id' => $secondUser->id, 'office_id' => $secondOffice->id, 'step_type' => 'signatory', 'step_label' => 'Second review', 'sequence' => 2]);

        $this->assertFalse(app(DocumentQueryService::class)->receivedBy(Document::query(), $secondUser)->whereKey($document)->exists());
    }
}
