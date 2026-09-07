<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentStep;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTrackingViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejected_step_shows_a_red_x_and_later_steps_have_no_symbol(): void
    {
        $office = Office::create(['name' => 'Review Office', 'abbreviation' => 'RO']);
        $user = User::factory()->create(['office_id' => $office->id]);
        $office->update(['head_id' => $user->id]);
        $type = DocumentType::create(['name' => 'Memorandum', 'abbreviation' => 'MEMO']);
        $document = Document::create([
            'document_number' => 'MEMO-TRACK-1',
            'from_id' => $office->id,
            'document_type_id' => $type->id,
            'subject' => 'Rejected tracking state',
            'content' => 'Test',
            'created_by' => $user->id,
            'status' => 'Rejected',
        ]);

        DocumentStep::create([
            'document_id' => $document->id, 'user_id' => $user->id, 'office_id' => $office->id,
            'step_type' => 'routing', 'step_label' => 'Initial review', 'sequence' => 1,
            'status' => 'Reviewed', 'processed_at' => now()->subHour(),
        ]);
        DocumentStep::create([
            'document_id' => $document->id, 'user_id' => $user->id, 'office_id' => $office->id,
            'step_type' => 'signatory', 'step_label' => 'Approval', 'sequence' => 2,
            'status' => 'Rejected', 'processed_at' => now(),
        ]);
        DocumentStep::create([
            'document_id' => $document->id, 'user_id' => $user->id, 'office_id' => $office->id,
            'step_type' => 'routing', 'step_label' => 'Release', 'sequence' => 3,
            'status' => 'Pending',
        ]);

        $html = view('livewire.documents.track-document', [
            'document' => $document->load('documentType', 'steps.user.office', 'steps.office', 'logs'),
        ])->render();

        $this->assertStringContainsString('status-icon rejected', $html);
        $this->assertStringContainsString('<i class="fas fa-times"></i>', $html);
        $this->assertStringNotContainsString('<i class="fas fa-hourglass-half"></i>', $html);
        $this->assertStringNotContainsString('<i class="fas fa-clock"></i>', $html);
    }
}
