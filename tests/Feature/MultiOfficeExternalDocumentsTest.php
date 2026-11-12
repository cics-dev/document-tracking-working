<?php

use App\Livewire\Documents\{CreateDocument, ListExternalDocuments, ViewExternalDocument};
use App\Models\{Document, DocumentGenerationRule, DocumentType, ExternalDocument, Office, Permission, Role, User};
use App\Services\NotificationCountService;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('attachments/test.pdf', '%PDF-1.4 test');
    $this->supplyRole = Role::create(['role' => 'external-supply-head', 'description' => 'Supply Head']);
    $this->saoRole = Role::create(['role' => 'external-sao-head', 'description' => 'SAO Head']);
    foreach ([$this->supplyRole, $this->saoRole] as $role) {
        $role->permissions()->attach(Permission::whereIn('key', ['send_documents', 'receive_external_documents', 'send_external_documents'])->pluck('id'));
    }
    $this->sao = Office::create(['name' => 'SAO Administration', 'abbreviation' => 'SAO', 'document_number_tag' => 'ADMIN', 'office_type' => 'ADMIN']);
    $this->supply = Office::create(['name' => 'Supply Office', 'abbreviation' => 'SUPPLY', 'document_number_tag' => 'ADMIN', 'office_type' => 'ADMIN']);
    $this->other = Office::create(['name' => 'Unrelated Office', 'abbreviation' => 'OTHER', 'office_type' => 'ADMIN']);
    $this->actor = User::factory()->create(['office_id' => $this->sao->id, 'role_id' => $this->supplyRole->id]);
    $head = User::factory()->create(['office_id' => $this->sao->id, 'role_id' => $this->saoRole->id]);
    $this->sao->update(['head_id' => $head->id, 'acting_head_id' => $this->actor->id]);
    $this->supply->update(['head_id' => $this->actor->id]);
    $this->letters = collect([$this->supply, $this->sao, $this->other])->map(fn ($office) => ExternalDocument::create([
        'document_number' => 'EXT-'.$office->id, 'from' => 'Agency', 'to_id' => $office->id,
        'subject' => $office->name.' letter', 'received_date' => now(), 'file_url' => 'attachments/test.pdf', 'file_type' => 'pdf',
    ]));
    $this->actingAs($this->actor);
});

it('lists and filters external letters for both represented offices and resets pagination', function () {
    Livewire::test(ListExternalDocuments::class)->assertSee('Receiving office:')->assertSee('All my offices')
        ->assertSee('Supply Office letter')->assertSee('SAO Administration letter')->assertDontSee('Unrelated Office')
        ->call('setPage', 2)->set('officeFilter', (string) $this->supply->id)->assertSet('paginators.page', 1)
        ->assertSee('Supply Office letter')->assertDontSee('SAO Administration letter')
        ->set('officeFilter', (string) $this->sao->id)->assertSee('SAO Administration letter')->assertDontSee('Supply Office letter')
        ->call('resetFilters')->assertSet('officeFilter', '')->assertSee('Supply Office letter')->assertSee('SAO Administration letter');
    expect(app(NotificationCountService::class)->for($this->actor)['external'])->toBe(2);
    Livewire::test(ListExternalDocuments::class)->set('officeFilter', (string) $this->other->id)->assertForbidden();
});

it('allows detail and preview for both offices but rejects an unrelated office', function () {
    foreach ($this->letters->take(2) as $letter) {
        Livewire::test(ViewExternalDocument::class, ['id' => $letter->id])->assertSet('document.id', $letter->id);
        $this->get(route('documents.external-document-preview', $letter))->assertOk();
    }
    $otherLetter = $this->letters->last();
    Livewire::test(ViewExternalDocument::class, ['id' => $otherLetter->id])->assertForbidden();
    $this->get(route('documents.external-document-preview', $otherLetter))->assertForbidden();
});

it('uses the receiving office when generating an external response', function (string $officeName, string $abbreviation) {
    $office = $officeName === 'Supply' ? $this->supply : $this->sao;
    $letter = $this->letters->firstWhere('to_id', $office->id);
    $type = DocumentType::create(['name' => 'Response '.$abbreviation, 'abbreviation' => $abbreviation, 'recipient_mode' => 'text', 'requires_signatories' => false, 'is_publicly_creatable' => true]);
    $rule = DocumentGenerationRule::create(['source_context' => 'external', 'target_document_type_id' => $type->id, 'button_label' => 'Generate '.$abbreviation, 'requires_assigned_office' => true]);
    $rule->roles()->attach([$this->supplyRole->id, $this->saoRole->id]);
    Livewire::test(ViewExternalDocument::class, ['id' => $letter->id])->assertSee('Generate '.$abbreviation)
        ->call('generateDocument', $rule->id)->assertRedirect(route('documents.create-document'));
    Livewire::test(CreateDocument::class)->assertSet('document_from_id', $office->id)->assertSee('From office')
        ->set('content', 'Response body')->call('submitDocument', 'send')->assertHasNoErrors();
    $document = Document::where('document_type_id', $type->id)->firstOrFail();
    expect($document->from_id)->toBe($office->id);
    expect($document->created_by)->toBe($this->actor->id);
    expect($document->document_number)->toStartWith($office->abbreviation.'(ADMIN)-'.$abbreviation.'-');
})->with([['Supply', 'RLM'], ['SAO', 'RLM'], ['Supply', 'ECLR'], ['SAO', 'ECLR']]);

it('honors external permissions separately for each office', function () {
    $this->supplyRole->permissions()->detach(Permission::whereIn('key', ['receive_external_documents', 'send_external_documents'])->pluck('id'));
    Livewire::test(ListExternalDocuments::class)->assertSee('SAO Administration letter')->assertDontSee('Supply Office letter');
    Livewire::test(ViewExternalDocument::class, ['id' => $this->letters->first()->id])->assertForbidden();
    expect(app(NotificationCountService::class)->for($this->actor)['external'])->toBe(1);
});

it('retains all-office access for Records users with global document permission', function () {
    $this->supplyRole->permissions()->attach(Permission::where('key', 'view_all_documents')->firstOrFail());
    Livewire::test(ListExternalDocuments::class)->assertSee('Unrelated Office letter')
        ->set('officeFilter', (string) $this->other->id)->assertSee('Unrelated Office letter')->assertDontSee('Supply Office letter');
    expect(app(NotificationCountService::class)->for($this->actor)['external'])->toBe(3);
    Livewire::test(ViewExternalDocument::class, ['id' => $this->letters->last()->id])->assertStatus(200);
    expect(app(NotificationCountService::class)->for($this->actor)['external'])->toBe(2);
});


it('does not add office labels or a personal office filter for a single-office head or OIC', function () {
    $this->supply->update(['head_id' => null]);
    $this->actor->update(['office_id' => $this->supply->id]);
    expect($this->actor->workflowOfficeIds())->toHaveCount(2);
    expect($this->actor->managesMultipleOffices())->toBeFalse();
    Livewire::test(ListExternalDocuments::class)->assertDontSee('Receiving office:')->assertDontSee('Search offices...');
});
