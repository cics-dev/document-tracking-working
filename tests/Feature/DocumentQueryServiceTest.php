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

    public function test_sent_list_merges_documents_created_by_the_user_with_documents_from_every_office_they_act_for(): void
    {
        $type = DocumentType::create(['name' => 'Office Letter', 'abbreviation' => 'OL']);
        $homeOffice = Office::create(['name' => 'Home Office', 'abbreviation' => 'HO', 'office_type' => 'ADMIN']);
        $actingOffice = Office::create(['name' => 'Acting Office', 'abbreviation' => 'AO', 'office_type' => 'ADMIN']);
        $otherOffice = Office::create(['name' => 'Other Office', 'abbreviation' => 'OO', 'office_type' => 'ADMIN']);
        $oic = User::factory()->create(['office_id' => $homeOffice->id]);
        $actingOffice->update(['acting_head_id' => $oic->id]);
        $otherUser = User::factory()->create(['office_id' => $otherOffice->id]);

        $officeDocument = Document::create(['document_number' => 'AO-OL-1', 'from_id' => $actingOffice->id, 'document_type_id' => $type->id, 'subject' => 'Office history', 'content' => 'Test', 'created_by' => $otherUser->id, 'status' => 'Sent']);
        $createdDocument = Document::create(['document_number' => 'OO-OL-1', 'from_id' => $otherOffice->id, 'document_type_id' => $type->id, 'subject' => 'Created personally', 'content' => 'Test', 'created_by' => $oic->id, 'status' => 'Sent']);
        $unrelatedDocument = Document::create(['document_number' => 'OO-OL-2', 'from_id' => $otherOffice->id, 'document_type_id' => $type->id, 'subject' => 'Unrelated', 'content' => 'Test', 'created_by' => $otherUser->id, 'status' => 'Sent']);

        $ids = app(DocumentQueryService::class)->listFor($oic, 'Sent')->pluck('id');

        $this->assertTrue($ids->contains($officeDocument->id));
        $this->assertTrue($ids->contains($createdDocument->id));
        $this->assertFalse($ids->contains($unrelatedDocument->id));
    }

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

    public function test_president_can_see_an_il_when_the_president_step_is_ready(): void
    {
        $type = DocumentType::create(['name' => 'Indorsement Letter', 'abbreviation' => 'IL']);
        $senderOffice = Office::create(['name' => 'Sender', 'abbreviation' => 'S', 'office_type' => 'ADMIN']);
        $presidentOffice = Office::create(['name' => 'President', 'abbreviation' => 'OP', 'office_type' => 'ADMIN']);
        $sender = User::factory()->create(['office_id' => $senderOffice->id]);
        $president = User::factory()->create(['office_id' => $presidentOffice->id, 'position' => 'University President']);
        $presidentOffice->update(['head_id' => $president->id]);
        $document = Document::create([
            'document_number' => 'S-IL-1-2026', 'from_id' => $senderOffice->id,
            'document_type_id' => $type->id, 'subject' => 'IL', 'content' => 'Test',
            'created_by' => $sender->id, 'status' => 'Sent',
        ]);
        DocumentStep::create([
            'document_id' => $document->id, 'user_id' => $president->id, 'office_id' => $presidentOffice->id,
            'step_type' => 'signatory', 'step_label' => 'Approved by', 'sequence' => 1,
        ]);

        $this->assertTrue(app(DocumentQueryService::class)->receivedBy(Document::query(), $president)->whereKey($document)->exists());
    }

    public function test_direct_recipient_cannot_bypass_earlier_pending_workflow_steps(): void
    {
        $type = DocumentType::create(['name' => 'Request Letter Memorandum', 'abbreviation' => 'RLM']);
        $senderOffice = Office::create(['name' => 'Sender', 'abbreviation' => 'S', 'office_type' => 'ADMIN']);
        $routingOffice = Office::create(['name' => 'Routing', 'abbreviation' => 'R', 'office_type' => 'ADMIN']);
        $presidentOffice = Office::create(['name' => 'President', 'abbreviation' => 'OP', 'office_type' => 'ADMIN']);
        $sender = User::factory()->create(['office_id' => $senderOffice->id]);
        $reviewer = User::factory()->create(['office_id' => $routingOffice->id]);
        $president = User::factory()->create(['office_id' => $presidentOffice->id, 'position' => 'University President']);
        $document = Document::create([
            'document_number' => 'S-RLM-1-2026', 'from_id' => $senderOffice->id, 'to_id' => $presidentOffice->id,
            'document_type_id' => $type->id, 'subject' => 'RLM', 'content' => 'Test',
            'created_by' => $sender->id, 'status' => 'Sent',
        ]);
        DocumentStep::create(['document_id' => $document->id, 'user_id' => $reviewer->id, 'office_id' => $routingOffice->id, 'step_type' => 'routing', 'step_label' => 'Review', 'sequence' => 1]);
        DocumentStep::create(['document_id' => $document->id, 'user_id' => $president->id, 'office_id' => $presidentOffice->id, 'step_type' => 'signatory', 'step_label' => 'Approved by', 'sequence' => 2]);

        $service = app(DocumentQueryService::class);
        $this->assertFalse($service->receivedBy(Document::query(), $president)->whereKey($document)->exists());

        $document->steps()->where('sequence', 1)->update(['status' => 'Reviewed', 'processed_at' => now()]);
        $this->assertTrue($service->receivedBy(Document::query(), $president)->whereKey($document)->exists());
    }
}
