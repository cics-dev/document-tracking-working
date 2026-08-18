<?php

namespace Tests\Feature;

use App\Http\Controllers\DocumentTypeController;
use App\Models\Document;
use App\Models\DocumentStep;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\DocumentWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
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

    public function test_oic_routing_for_an_existing_head_assignment_preserves_head_and_records_for_signature(): void
    {
        [$document, $head] = $this->documentWithStep('routing');
        $head->update(['name' => 'Maria Clara', 'position' => 'Vice President']);
        $step = $document->steps()->firstOrFail();
        $step->update([
            'assigned_user_id' => $head->id,
            'signatory_name' => $head->name,
            'signatory_position' => $head->position,
        ]);
        $oic = User::factory()->create([
            'name' => 'Elena Garcia',
            'office_id' => $head->office_id,
            'signature' => 'signatures/elena.png',
        ]);
        Office::findOrFail($head->office_id)->update(['acting_head_id' => $oic->id]);

        app(DocumentWorkflowService::class)->approve($document, $oic);
        $step->refresh();

        $this->assertSame('Maria Clara', $step->signatory_name);
        $this->assertSame('Vice President', $step->signatory_position);
        $this->assertSame('signatures/elena.png', $step->signature_path);
        $this->assertTrue($step->signed_for);

        $routingSlip = Blade::render(
            '<x-routing-slip recipient="Motorpool" remarks="Checked" :head="$head" :date="$date" :signature="$signature" :signed-for="true" />',
            ['head' => $step->signatory_name, 'date' => $step->processed_at, 'signature' => $step->signature_path]
        );
        $this->assertStringContainsString('Maria Clara', $routingSlip);
        $this->assertStringContainsString('signatures/elena.png', $routingSlip);
        $this->assertStringContainsString('>for</span>', $routingSlip);
        $this->assertStringNotContainsString('Elena Garcia</p>', $routingSlip);
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

    public function test_oic_signs_for_the_snapshotted_head_without_replacing_the_signatory(): void
    {
        [$document, $head] = $this->documentWithStep('signatory');
        $head->update(['name' => 'Original Head', 'position' => 'Director']);
        $step = $document->steps()->firstOrFail();
        $step->update(['signatory_name' => $head->name, 'signatory_position' => $head->position]);
        $oic = User::factory()->create(['office_id' => $head->office_id, 'signature' => 'signatures/oic.png']);
        Office::findOrFail($head->office_id)->update(['acting_head_id' => $oic->id]);

        app(DocumentWorkflowService::class)->approve($document, $oic);
        $step->refresh();

        $this->assertSame('Original Head', $step->signatory_name);
        $this->assertSame('Director', $step->signatory_position);
        $this->assertSame('signatures/oic.png', $step->signature_path);
        $this->assertTrue($step->signed_for);

        $head->update(['name' => 'Changed Later']);
        $this->assertSame('Original Head', $step->fresh()->signatory_name);
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

    public function test_oic_inherits_every_access_right_from_the_designated_head_role(): void
    {
        [$document, $head] = $this->documentWithStep('routing');
        $oicRole = Role::create(['role' => 'Staff', 'description' => 'OIC stored role']);
        $headRole = Role::create(['role' => 'Office Head', 'description' => 'Inherited role']);
        $headPermission = Permission::firstOrCreate(['key' => 'send_documents'], ['label' => 'Send documents']);
        $staffPermission = Permission::firstOrCreate(['key' => 'manage_users'], ['label' => 'Manage users']);
        $headRole->permissions()->attach($headPermission);
        $oicRole->permissions()->attach($staffPermission);
        $headDocumentType = DocumentType::create(['name' => 'Head Document', 'abbreviation' => 'HD']);
        $staffDocumentType = DocumentType::create(['name' => 'Staff Document', 'abbreviation' => 'SD']);
        DB::table('role_document_types')->insert([
            ['role_id' => $headRole->id, 'document_type_id' => $headDocumentType->id, 'is_allowed' => true],
            ['role_id' => $oicRole->id, 'document_type_id' => $staffDocumentType->id, 'is_allowed' => true],
        ]);
        $head->update(['role_id' => $headRole->id]);
        $oic = User::factory()->create(['office_id' => $head->office_id, 'role_id' => $oicRole->id]);
        Office::findOrFail($head->office_id)->update(['acting_head_id' => $oic->id]);

        $this->assertTrue($oic->hasAccess('send_documents'));
        $this->assertFalse($oic->hasAccess('manage_users'));
        $allowedDocumentTypeIds = app(DocumentTypeController::class)->index($oic)->pluck('id');
        $this->assertTrue($allowedDocumentTypeIds->contains($headDocumentType->id));
        $this->assertFalse($allowedDocumentTypeIds->contains($staffDocumentType->id));
        $this->assertSame($oicRole->id, $oic->fresh()->role_id);
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
