<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentStep;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Services\DocumentWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DocumentWorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_routing_step_moves_document_into_process_and_records_audit_log(): void
    {
        [$document, $actor] = $this->documentWithStep('routing');
        DocumentStep::create([
            'document_id' => $document->id,
            'user_id' => $actor->id,
            'office_id' => $actor->office_id,
            'step_type' => 'signatory',
            'step_label' => 'Approval',
            'sequence' => 2,
        ]);

        $result = app(DocumentWorkflowService::class)->approve($document, $actor, 'Checked.');

        $this->assertFalse($result['completed']);
        $this->assertDatabaseHas('document_steps', ['id' => $result['step']->id, 'status' => 'Reviewed']);
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'status' => 'In Process']);
        $this->assertDatabaseHas('document_logs', ['document_id' => $document->id, 'user_id' => $actor->id, 'action' => 'signed']);
    }

    public function test_final_signatory_step_approves_document(): void
    {
        [$document, $actor] = $this->documentWithStep('signatory');

        $result = app(DocumentWorkflowService::class)->approve($document, $actor);

        $this->assertTrue($result['completed']);
        $this->assertDatabaseHas('document_steps', ['id' => $result['step']->id, 'status' => 'Approved']);
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'status' => 'Approved']);
    }

    public function test_rejecting_a_routing_step_returns_document(): void
    {
        [$document, $actor] = $this->documentWithStep('routing');

        app(DocumentWorkflowService::class)->reject($document, $actor, 'Please revise.');

        $this->assertDatabaseHas('document_steps', ['document_id' => $document->id, 'status' => 'Returned']);
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'status' => 'Returned']);
    }

    public function test_officer_in_charge_can_process_a_pending_office_step(): void
    {
        [$document, $head] = $this->documentWithStep('routing');
        $oic = User::factory()->create(['office_id' => $head->office_id]);
        Office::findOrFail($head->office_id)->update(['acting_head_id' => $oic->id]);

        app(DocumentWorkflowService::class)->approve($document, $oic);

        $this->assertDatabaseHas('document_steps', [
            'document_id' => $document->id,
            'user_id' => $oic->id,
            'status' => 'Reviewed',
        ]);
    }

    public function test_designated_head_keeps_visibility_but_cannot_act_while_an_oic_is_active(): void
    {
        [$document, $head] = $this->documentWithStep('routing');
        $oic = User::factory()->create(['office_id' => $head->office_id]);
        Office::findOrFail($head->office_id)->update(['acting_head_id' => $oic->id]);

        try {
            app(DocumentWorkflowService::class)->approve($document, $head);
            $this->fail('The designated head should not be able to act while an OIC is active.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }

    public function test_oic_inherits_head_role_without_changing_stored_role(): void
    {
        [$document, $head] = $this->documentWithStep('routing');
        $oic = User::factory()->create(['office_id' => $head->office_id, 'role_id' => null]);
        $headRole = Role::create(['role' => 'Workflow Head', 'description' => 'Test role']);
        $head->update(['role_id' => $headRole->id]);
        Office::findOrFail($head->office_id)->update(['acting_head_id' => $oic->id]);

        $this->assertSame($headRole->id, $oic->effectiveRoleId());
        $this->assertNull($oic->fresh()->role_id);
    }

    public function test_oic_can_inherit_permissions_for_an_office_different_from_their_home_office(): void
    {
        [$document, $head] = $this->documentWithStep('routing');
        $staffOffice = Office::create(['name' => 'Staff Office', 'abbreviation' => 'ST', 'office_type' => 'ADMIN']);
        $oic = User::factory()->create(['office_id' => $staffOffice->id, 'role_id' => null]);
        $headRole = Role::create(['role' => 'Office Head', 'description' => 'Test role']);
        $head->update(['role_id' => $headRole->id]);
        Office::findOrFail($head->office_id)->update(['acting_head_id' => $oic->id]);

        $this->assertTrue($oic->isActingHead());
        $this->assertSame($headRole->id, $oic->effectiveRoleId());
        $this->assertContains($head->office_id, $oic->workflowOfficeIds());
        app(DocumentWorkflowService::class)->approve($document, $oic);
    }

    /** @return array{Document, User} */
    private function documentWithStep(string $stepType): array
    {
        $office = Office::create(['name' => 'Workflow Office', 'abbreviation' => 'WO', 'office_type' => 'ADMIN']);
        $actor = User::factory()->create(['office_id' => $office->id]);
        $office->update(['head_id' => $actor->id]);
        $type = DocumentType::create(['name' => 'Workflow Letter', 'abbreviation' => 'WL']);

        $document = Document::create([
            'document_number' => 'WO-WL-1-2026',
            'from_id' => $office->id,
            'to_id' => $office->id,
            'document_type_id' => $type->id,
            'subject' => 'Workflow test',
            'content' => 'Test content',
            'created_by' => $actor->id,
            'status' => 'Sent',
        ]);

        DocumentStep::create([
            'document_id' => $document->id,
            'user_id' => $actor->id,
            'office_id' => $office->id,
            'step_type' => $stepType,
            'step_label' => 'Review',
            'sequence' => 1,
        ]);

        return [$document, $actor];
    }
}
