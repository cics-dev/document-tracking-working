<?php

namespace Tests\Feature;

use App\Livewire\AccessRights\ManageAccessRights;
use App\Livewire\DocumentFlows\ManageDocumentFlows;
use App\Livewire\Documents\CreateDocument;
use App\Livewire\DocumentTypes\ManageDocumentTypes;
use App\Livewire\Offices\CreateOffice;
use App\Livewire\Roles\ManageRoles;
use App\Livewire\Users\CreateUser;
use App\Models\Document;
use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\DocumentPreviewDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SystemManagementRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_management_and_document_creation_forms_render_without_runtime_errors(): void
    {
        $role = Role::create(['role' => 'system-admin', 'description' => 'System Administrator']);
        foreach (['manage_document_flows', 'manage_roles', 'manage_access_rights', 'manage_offices', 'manage_users', 'send_documents'] as $key) {
            $role->permissions()->attach(Permission::firstOrCreate(['key' => $key], ['label' => str($key)->headline()]));
        }

        $office = Office::create(['name' => 'Test Office', 'abbreviation' => 'TEST', 'office_type' => 'ADMIN']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'office_id' => $office->id,
            'signature' => 'signatures/test-sender.png',
        ]);
        $office->update(['head_id' => $user->id]);
        $type = DocumentType::create([
            'name' => 'Dynamic Test Document', 'abbreviation' => 'DTD', 'recipient_mode' => 'office',
            'recipient_label' => 'To', 'recipient_office_id' => $office->id, 'document_level' => 'Inter',
            'show_thru' => true, 'show_carbon_copy' => true, 'requires_signatories' => true,
        ]);
        DB::table('role_document_types')->insert([
            'role_id' => $role->id, 'document_type_id' => $type->id, 'is_allowed' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DocumentFlowStage::create([
            'document_type_id' => $type->id, 'office_id' => $office->id, 'stage_type' => 'routing',
            'label' => 'Test review', 'description' => 'Runtime rendering check', 'sequence' => 10,
            'is_required' => false, 'is_selectable' => true,
        ]);

        $this->actingAs($user);

        foreach ([ManageAccessRights::class, ManageDocumentFlows::class, ManageDocumentTypes::class, ManageRoles::class, CreateOffice::class, CreateUser::class] as $component) {
            Livewire::test($component)->assertStatus(200);
        }

        Livewire::test(ManageDocumentTypes::class)
            ->assertSet('recipient_mode', '')
            ->assertSet('document_level', '')
            ->assertSet('print_layout', '')
            ->assertSet('sender_signature_policy', '')
            ->assertSet('approver_display_mode', '')
            ->assertSet('show_thru', false)
            ->assertSet('show_carbon_copy', false)
            ->assertSet('allow_attachments', false)
            ->assertSet('allow_oic_signature', false)
            ->assertSee('Choose print layout...');

        Livewire::test(ManageDocumentFlows::class)
            ->assertSet('documentTypeId', '')
            ->assertSet('stageType', '')
            ->assertSet('isRequired', false)
            ->assertSet('isSelectable', false)
            ->assertSet('newConditionType', '')
            ->assertSet('generationContext', '')
            ->assertSet('generationRequiresAssignment', false)
            ->assertSee('Choose stage type...');

        Livewire::test(ManageDocumentTypes::class)
            ->call('edit', $type->id)
            ->assertSet('print_layout', 'memorandum')
            ->set('print_layout', 'letter')
            ->assertSet('sender_signature_policy', 'approved')
            ->set('sender_signature_policy', 'always')
            ->set('approver_display_mode', 'hidden')
            ->set('allow_oic_signature', false)
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('document_types', [
            'id' => $type->id,
            'print_layout' => 'letter',
            'sender_signature_policy' => 'always',
            'approver_display_mode' => 'hidden',
            'allow_oic_signature' => false,
        ]);

        $document = Document::create([
            'document_number' => 'DTD-1', 'from_id' => $office->id, 'to_id' => $office->id,
            'document_type_id' => $type->id, 'subject' => 'Layout test', 'content' => 'Test',
            'created_by' => $user->id,
        ]);
        $previewData = app(DocumentPreviewDataService::class)->build($document);
        $this->assertSame('letter', $previewData['printLayout']);
        $this->assertSame('always', $previewData['senderSignaturePolicy']);
        $this->assertSame('hidden', $previewData['approverDisplayMode']);
        $this->assertSame('signatures/test-sender.png', $previewData['fromSignature']);

        Livewire::test(CreateDocument::class)
            ->set('document_type_id', (string) $type->id)
            ->call('handleUpdateDocumentType')
            ->call('addSignatory', ['role' => 'Reviewed by', 'office_id' => $office->id, 'locked' => false])
            ->assertSet('signatories.0.role_type', 'Reviewed by')
            ->set('signatories.0.role_type', 'Recommending Approval')
            ->assertSet('signatories.0.role', 'Recommending Approval')
            ->set('signatories.0.role_type', 'custom')
            ->assertSet('signatories.0.role', '')
            ->assertSet('document_to_id', $office->id)
            ->assertSee('Custom label')
            ->assertSee('Enter custom label...')
            ->assertSee('Runtime rendering check')
            ->assertStatus(200);
    }

    public function test_required_flow_signatory_is_loaded_without_a_fixed_recipient(): void
    {
        $role = Role::create(['role' => 'sender', 'description' => 'Sender']);
        $role->permissions()->attach(Permission::firstOrCreate(
            ['key' => 'send_documents'],
            ['label' => 'Send Documents']
        ));
        $senderOffice = Office::create(['name' => 'Sender Office', 'abbreviation' => 'SEND', 'office_type' => 'ADMIN']);
        $sender = User::factory()->create(['role_id' => $role->id, 'office_id' => $senderOffice->id]);
        $senderOffice->update(['head_id' => $sender->id]);

        $presidentOffice = Office::create(['name' => 'Office of the University President', 'abbreviation' => 'OP']);
        $president = User::factory()->create(['office_id' => $presidentOffice->id]);
        $presidentOffice->update(['head_id' => $president->id]);

        $type = DocumentType::create([
            'name' => 'Inter-Office Memorandum',
            'abbreviation' => 'IOM',
            'recipient_mode' => 'office',
            'recipient_office_id' => null,
            'requires_signatories' => true,
        ]);
        DB::table('role_document_types')->insert([
            'role_id' => $role->id,
            'document_type_id' => $type->id,
            'is_allowed' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DocumentFlowStage::create([
            'document_type_id' => $type->id,
            'office_id' => $presidentOffice->id,
            'stage_type' => 'signatory',
            'label' => 'Approved by',
            'sequence' => 10,
            'is_required' => true,
            'is_selectable' => true,
        ]);

        $this->actingAs($sender);

        Livewire::test(CreateDocument::class)
            ->set('document_type_id', (string) $type->id)
            ->call('handleUpdateDocumentType')
            ->assertSet('signatories.0.role', 'Approved by')
            ->assertSet('signatories.0.office_id', $presidentOffice->id)
            ->assertSet('signatories.0.locked', true);
    }

    public function test_intra_creation_selects_the_intra_type_and_does_not_require_signatories(): void
    {
        $role = Role::create(['role' => 'sender', 'description' => 'Sender']);
        $role->permissions()->attach(Permission::firstOrCreate(
            ['key' => 'send_documents'],
            ['label' => 'Send Documents']
        ));
        $office = Office::create(['name' => 'Records Office', 'abbreviation' => 'REC', 'office_type' => 'ADMIN']);
        $sender = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $office->update(['head_id' => $sender->id]);
        $type = DocumentType::create([
            'name' => 'Intra-Office Memorandum',
            'abbreviation' => 'Intra',
            'document_level' => 'Intra',
            'recipient_mode' => 'none',
            'requires_signatories' => true,
        ]);
        DB::table('role_document_types')->insert([
            'role_id' => $role->id,
            'document_type_id' => $type->id,
            'is_allowed' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DocumentFlowStage::create([
            'document_type_id' => $type->id,
            'office_id' => $office->id,
            'stage_type' => 'signatory',
            'label' => 'Approved by',
            'sequence' => 10,
            'is_required' => true,
            'is_selectable' => false,
        ]);

        $this->actingAs($sender);

        Livewire::withQueryParams(['level' => 'Intra'])
            ->test(CreateDocument::class)
            ->assertSet('document_type_id', (string) $type->id)
            ->assertSet('document_type', 'Intra')
            ->assertSet('signatories', [])
            ->assertDontSee('Signatories')
            ->set('subject', 'Internal notice')
            ->set('content', 'For office information only.')
            ->call('submitDocument', 'send')
            ->assertHasNoErrors();

        $document = Document::where('subject', 'Internal notice')->firstOrFail();
        $this->assertSame('Intra', $document->document_level);
        $this->assertCount(0, $document->steps);
    }
}
