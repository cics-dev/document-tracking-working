<?php

namespace Tests\Feature;

use App\Livewire\Documents\CreateDocument;
use App\Livewire\Documents\ListDocuments;
use App\Models\Document;
use App\Models\DocumentFlowStage;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentRevisionVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_revise_button_is_only_visible_to_the_document_writer_in_sent_documents(): void
    {
        $role = Role::create(['role' => 'writer', 'description' => 'Document writer']);
        foreach ([
            'send_documents' => 'Send Documents',
            'receive_documents' => 'Receive Documents',
            'view_all_documents' => 'View All Documents',
        ] as $key => $label) {
            $role->permissions()->attach(Permission::firstOrCreate(['key' => $key], ['label' => $label]));
        }

        $office = Office::create(['name' => 'Writing Office', 'abbreviation' => 'WO', 'office_type' => 'ADMIN']);
        $writer = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $type = DocumentType::create(['name' => 'Revision Letter', 'abbreviation' => 'RL']);
        $document = Document::create([
            'document_number' => 'WO-RL-1-2026',
            'from_id' => $office->id,
            'to_id' => $office->id,
            'document_type_id' => $type->id,
            'subject' => 'Rejected document',
            'content' => 'Test',
            'created_by' => $writer->id,
            'status' => 'Rejected',
        ]);

        $this->actingAs($otherUser);
        Livewire::test(ListDocuments::class, ['mode' => 'Sent'])
            ->assertSee($document->document_number)
            ->assertDontSee('Revise');

        $this->actingAs($writer);
        Livewire::test(ListDocuments::class, ['mode' => 'Sent'])
            ->assertSee($document->document_number)
            ->assertSee('Revise');
        Livewire::test(ListDocuments::class, ['mode' => 'received'])
            ->assertSee($document->document_number)
            ->assertDontSee('Revise');
        Livewire::test(ListDocuments::class, ['mode' => 'all'])
            ->assertSee($document->document_number)
            ->assertDontSee('Revise');
    }

    public function test_revising_a_generated_iom_uses_the_iom_as_the_revision_root(): void
    {
        $role = Role::create(['role' => 'iom-writer', 'description' => 'IOM writer']);
        $role->permissions()->attach(Permission::firstOrCreate(
            ['key' => 'send_documents'],
            ['label' => 'Send Documents']
        ));
        $office = Office::create(['name' => 'IOM Office', 'abbreviation' => 'IO', 'office_type' => 'ADMIN']);
        $writer = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $office->update(['head_id' => $writer->id]);
        $rlmType = DocumentType::create(['name' => 'Request Letter Memorandum', 'abbreviation' => 'RLM']);
        $iomType = DocumentType::create(['name' => 'Inter-Office Memorandum', 'abbreviation' => 'IOM']);
        DB::table('role_document_types')->insert([
            'role_id' => $role->id,
            'document_type_id' => $iomType->id,
            'is_allowed' => true,
        ]);

        $rlm = Document::create([
            'document_number' => 'RLM-1-2026', 'from_id' => $office->id,
            'document_type_id' => $rlmType->id, 'subject' => 'Request', 'content' => 'Test',
            'created_by' => $writer->id, 'status' => 'Approved',
        ]);
        $iom = Document::create([
            'document_number' => 'IOM-1-2026', 'from_id' => $office->id,
            'document_type_id' => $iomType->id, 'subject' => 'Generated IOM', 'content' => 'Test',
            'created_by' => $writer->id, 'status' => 'Rejected',
            'original_document_id' => $rlm->id, 'is_revision' => false,
        ]);

        $this->actingAs($writer);
        Livewire::test(CreateDocument::class, ['number' => $iom->document_number])
            ->assertSet('original_document_id', $iom->id)
            ->assertSet('original_document_number', 'IOM-1-2026')
            ->assertSet('revision_document_number', 'IOM-1a-2026')
            ->assertSet('document_type_id', $iomType->id);
    }

    public function test_only_writer_can_revise_a_returned_document(): void
    {
        $role = Role::create(['role' => 'document-author', 'description' => 'Document author']);
        $role->permissions()->attach(Permission::firstOrCreate(
            ['key' => 'send_documents'],
            ['label' => 'Send Documents']
        ));
        $office = Office::create(['name' => 'Author Office', 'abbreviation' => 'AO', 'office_type' => 'ADMIN']);
        $sender = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $writer = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $otherUser = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $office->update(['head_id' => $sender->id]);
        $budgetOffice = Office::create(['name' => 'Budget Office', 'abbreviation' => 'BO', 'office_type' => 'ADMIN']);
        $budgetReviewer = User::factory()->create(['role_id' => $role->id, 'office_id' => $budgetOffice->id]);
        $budgetOffice->update(['head_id' => $budgetReviewer->id]);
        $type = DocumentType::create(['name' => 'Returned Letter', 'abbreviation' => 'RTL']);
        DB::table('role_document_types')->insert([
            'role_id' => $role->id,
            'document_type_id' => $type->id,
            'is_allowed' => true,
        ]);
        $document = Document::create([
            'document_number' => 'RTL-1-2026', 'from_id' => $office->id, 'from_user_id' => $sender->id,
            'document_type_id' => $type->id, 'subject' => 'Returned document', 'content' => 'Test',
            'created_by' => $writer->id, 'status' => 'Returned',
        ]);
        $budgetCondition = WorkflowCondition::create([
            'key' => 'has_budget_implications', 'label' => 'Has budget implications?', 'input_type' => 'boolean',
        ]);
        DocumentFlowStage::create([
            'document_type_id' => $type->id, 'office_id' => $budgetOffice->id,
            'stage_type' => 'routing', 'label' => 'Budget Review', 'sequence' => 1,
            'is_required' => false, 'is_selectable' => true,
            'workflow_condition_id' => $budgetCondition->id, 'condition_value' => '1',
        ]);
        $document->steps()->create([
            'user_id' => $budgetReviewer->id, 'office_id' => $budgetOffice->id,
            'step_type' => 'routing', 'step_label' => 'Budget Review', 'sequence' => 1, 'status' => 'Returned',
        ]);

        $this->actingAs($writer);
        Livewire::test(ListDocuments::class, ['mode' => 'Sent'])->assertSee('Revise');
        Livewire::test(CreateDocument::class, ['number' => $document->document_number])
            ->assertSet('revision_document_number', 'RTL-1a-2026')
            ->assertSet("conditionValues.{$budgetCondition->id}", true);

        foreach ([$sender, $otherUser] as $nonWriter) {
            $this->actingAs($nonWriter);
            Livewire::test(ListDocuments::class, ['mode' => 'Sent'])->assertDontSee('Revise');
            $this->get(route('documents.create-revision', $document->document_number))->assertForbidden();
        }
    }
}
