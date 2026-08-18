<?php

namespace Tests\Feature;

use App\Livewire\Documents\CreateDocument;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class OicDocumentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_head_can_prepare_a_draft_but_only_the_oic_can_open_it_for_sending(): void
    {
        $role = Role::create(['role' => 'Office Head', 'description' => 'Office operations']);
        $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
        $office = Office::create(['name' => 'Draft Office', 'abbreviation' => 'DO', 'office_type' => 'ADMIN']);
        $head = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id]);
        $oic = User::factory()->create(['office_id' => $office->id]);
        $office->update(['head_id' => $head->id, 'acting_head_id' => $oic->id]);
        $type = DocumentType::create(['name' => 'Office Memorandum', 'abbreviation' => 'OM', 'recipient_mode' => 'none']);
        DB::table('role_document_types')->insert([
            'role_id' => $role->id,
            'document_type_id' => $type->id,
            'is_allowed' => true,
        ]);

        $this->actingAs($head);
        Livewire::test(CreateDocument::class)
            ->assertSee('You may save and preview this draft; the OIC must send it.')
            ->set('document_type_id', (string) $type->id)
            ->set('subject', 'Prepared by the head')
            ->call('submitDocument', 'Draft')
            ->assertHasNoErrors();

        $draft = Document::where('subject', 'Prepared by the head')->firstOrFail();
        $this->assertSame('Draft', $draft->status);
        $this->assertSame($head->id, $draft->created_by);
        $this->assertSame($office->id, $draft->from_id);

        Livewire::test(CreateDocument::class, ['draft_id' => $draft->id])
            ->call('submitDocument', 'send')
            ->assertHasErrors('document');
        $this->assertSame('Draft', $draft->fresh()->status);

        $this->actingAs($oic);
        Livewire::test(CreateDocument::class, ['draft_id' => $draft->id])
            ->assertSet('subject', 'Prepared by the head')
            ->assertStatus(200);
    }
}
