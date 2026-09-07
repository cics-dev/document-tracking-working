<?php

namespace Tests\Feature;

use App\Livewire\Documents\CreateDocument;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CreateDocumentTypeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_dropdown_only_shows_role_allowed_or_public_document_types(): void
    {
        $role = Role::create(['role' => 'limited-sender', 'description' => 'Limited Sender']);
        $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
        $office = Office::create(['name' => 'Limited Office', 'abbreviation' => 'LIMITED']);
        $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $office->update(['head_id' => $user->id]);

        $allowed = DocumentType::create(['name' => 'Allowed Memorandum', 'abbreviation' => 'ALLOW']);
        $public = DocumentType::create([
            'name' => 'Public Memorandum', 'abbreviation' => 'PUBLIC', 'is_publicly_creatable' => true,
        ]);
        $denied = DocumentType::create(['name' => 'Restricted Memorandum', 'abbreviation' => 'DENY']);
        DB::table('role_document_types')->insert([
            'role_id' => $role->id,
            'document_type_id' => $allowed->id,
            'is_allowed' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(CreateDocument::class)
            ->assertSet('types', function ($types) use ($allowed, $public, $denied) {
                $ids = collect($types)->pluck('id');

                return $ids->contains($allowed->id)
                    && $ids->contains($public->id)
                    && ! $ids->contains($denied->id);
            })
            ->assertSee('Allowed Memorandum')
            ->assertSee('Public Memorandum')
            ->assertDontSee('Restricted Memorandum');
    }
}
