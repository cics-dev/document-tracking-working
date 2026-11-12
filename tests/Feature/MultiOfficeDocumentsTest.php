<?php

use App\Livewire\Documents\{CreateDocument, ListDocuments};
use App\Models\{Document, DocumentType, Office, Permission, Role, User};
use App\Services\DocumentQueryService;
use Livewire\Livewire;

beforeEach(function () {
    $this->supplyRole = Role::create(['role' => 'supply-head-multi', 'description' => 'Supply Head']);
    $this->saoRole = Role::create(['role' => 'sao-head-multi', 'description' => 'SAO Head']);
    foreach ([$this->supplyRole, $this->saoRole] as $role) {
        $role->permissions()->attach(Permission::whereIn('key', ['send_documents', 'receive_documents'])->pluck('id'));
    }
    $this->sao = Office::create(['name' => 'SAO Administration', 'abbreviation' => 'SAO', 'document_number_tag' => 'ADMIN', 'office_type' => 'ADMIN']);
    $this->supply = Office::create(['name' => 'Supply Office', 'abbreviation' => 'SUPPLY', 'document_number_tag' => 'ADMIN', 'office_type' => 'ADMIN']);
    $this->actor = User::factory()->create(['office_id' => $this->sao->id, 'role_id' => $this->supplyRole->id]);
    $this->head = User::factory()->create(['office_id' => $this->sao->id, 'role_id' => $this->saoRole->id]);
    $this->sao->update(['head_id' => $this->head->id, 'acting_head_id' => $this->actor->id]);
    $this->supply->update(['head_id' => $this->actor->id]);
    $this->other = Office::create(['name' => 'Other', 'abbreviation' => 'OTHER', 'office_type' => 'ADMIN']);
    $this->otherUser = User::factory()->create(['office_id' => $this->other->id]);
    $this->other->update(['head_id' => $this->otherUser->id]);
    $this->type = DocumentType::create(['name' => 'Multi Office Memo', 'abbreviation' => 'MOM', 'recipient_mode' => 'none', 'requires_signatories' => false, 'is_publicly_creatable' => true]);
    $this->makeDocument = function ($office, $subject, $attributes = []) {
        return Document::create(array_merge(['document_number' => 'MULTI-'.Document::count(), 'document_type_id' => $this->type->id, 'from_id' => $this->other->id, 'to_id' => $office->id, 'created_by' => $this->otherUser->id, 'subject' => $subject, 'content' => 'Body', 'document_level' => 'Inter', 'status' => 'Sent', 'date_sent' => '2026-10-01'], $attributes));
    };
    $this->actingAs($this->actor);
});

it('includes Supply and SAO received history and filters by the receiving office', function () {
    $supply = ($this->makeDocument)($this->supply, 'Supply history');
    $supply->steps()->create(['user_id' => $this->otherUser->id, 'office_id' => $this->supply->id, 'step_type' => 'routing', 'step_label' => 'Old review', 'sequence' => 1, 'status' => 'Reviewed']);
    $sao = ($this->makeDocument)($this->sao, 'SAO direct receipt');
    $unrelated = ($this->makeDocument)($this->other, 'Unrelated receipt');
    expect($this->actor->workflowOfficeIds())->toContain($this->supply->id, $this->sao->id);
    Livewire::test(ListDocuments::class, ['mode' => 'received'])
        ->assertSee('All my offices')->assertSee('Receiving office:')
        ->assertViewHas('receivingOfficeLabels', fn ($labels) => $labels[$supply->id] === ['Supply Office'] && $labels[$sao->id] === ['SAO Administration'])
        ->assertSee('Supply history')->assertSee('SAO direct receipt')->assertDontSee('Unrelated receipt')
        ->set('officeFilter', (string) $this->supply->id)->assertSee('1 document')->assertSee('Supply history')->assertDontSee('SAO direct receipt')
        ->set('officeFilter', (string) $this->sao->id)->assertSee('SAO direct receipt')->assertDontSee('Supply history')
        ->call('resetFilters')->assertSet('officeFilter', '')->assertSee('2 documents');
    expect(app(DocumentQueryService::class)->canView($this->actor, $unrelated->document_number))->toBeFalse();
});

it('includes both issuing offices and personal creations in Sent', function () {
    ($this->makeDocument)($this->other, 'Supply issued', ['from_id' => $this->supply->id]);
    ($this->makeDocument)($this->other, 'SAO issued', ['from_id' => $this->sao->id]);
    ($this->makeDocument)($this->other, 'Personally created', ['created_by' => $this->actor->id]);
    Livewire::test(ListDocuments::class, ['mode' => 'Sent'])
        ->assertSee('Issuing office:')->assertSee('3 documents')->assertSee('Supply issued')->assertSee('SAO issued')->assertSee('Personally created')
        ->set('officeFilter', (string) $this->supply->id)->assertSee('1 document')->assertSee('Supply issued')->assertDontSee('SAO issued')->assertDontSee('Personally created')
        ->set('officeFilter', (string) $this->sao->id)->assertSee('SAO issued')->assertDontSee('Supply issued');
});

it('keeps actionable documents from either office above history', function () {
    $supply = ($this->makeDocument)($this->supply, 'Supply action');
    $supply->steps()->create(['user_id' => $this->actor->id, 'office_id' => $this->supply->id, 'step_type' => 'routing', 'step_label' => 'Review', 'sequence' => 1, 'status' => 'Pending']);
    $sao = ($this->makeDocument)($this->sao, 'SAO action');
    $sao->steps()->create(['user_id' => $this->head->id, 'office_id' => $this->sao->id, 'step_type' => 'routing', 'step_label' => 'Review', 'sequence' => 1, 'status' => 'Pending']);
    ($this->makeDocument)($this->supply, 'New history', ['date_sent' => '2026-10-05']);
    Livewire::test(ListDocuments::class, ['mode' => 'received'])
        ->assertViewHas('documents', fn ($rows) => $rows->take(2)->pluck('id')->sort()->values()->all() === collect([$supply->id, $sao->id])->sort()->values()->all());
});

it('requires an issuing office and uses that office for numbering and sender details', function (string $which) {
    $office = $which === 'Supply' ? $this->supply : $this->sao;
    $form = Livewire::test(CreateDocument::class)->assertSee('From office')->assertSee('Supply Office')->assertSee('SAO Administration')
        ->call('submitDocument', 'Draft')->assertHasErrors('document_from_id')
        ->set('document_from_id', (string) $office->id)->set('document_type_id', (string) $this->type->id)
        ->set('subject', 'Issued as '.$which)->set('content', 'Body')->call('previewDocument')->assertDispatched('open-preview-tab');
    $prefix = $office->abbreviation.'(ADMIN)-MOM-';
    $preview = collect(session()->all())->first(fn ($value) => is_array($value) && ($value['action'] ?? null) === 'preview');
    expect($preview['documentNumber'])->toStartWith($prefix);
    $form->call('submitDocument', 'send')->assertHasNoErrors();
    $saved = Document::where('subject', 'Issued as '.$which)->firstOrFail();
    expect($saved->from_id)->toBe($office->id);
    expect($saved->from_user_id)->toBe($this->actor->id);
    expect($saved->created_by)->toBe($this->actor->id);
    expect($saved->document_number)->toStartWith($prefix);
})->with(['Supply', 'SAO']);

it('uses each offices role for allowed document types and sending permissions', function () {
    $restricted = DocumentType::create(['name' => 'Supply only type', 'abbreviation' => 'SOT', 'recipient_mode' => 'none', 'requires_signatories' => false]);
    $this->supplyRole->role_document_types()->create(['document_type_id' => $restricted->id, 'is_allowed' => true]);
    $form = Livewire::test(CreateDocument::class)->set('document_from_id', (string) $this->supply->id)
        ->assertViewHas('senderOfficeOptions', fn ($offices) => $offices->count() === 2)
        ->assertSee('Supply only type')->set('document_type_id', (string) $restricted->id);
    $form->set('document_from_id', (string) $this->sao->id)->assertSet('document_type_id', '')->assertDontSee('Supply only type');
    $form->set('document_type_id', (string) $restricted->id)->set('subject', 'Wrong office type')
        ->call('submitDocument', 'Draft')->assertHasErrors('document_type_id');
    $this->saoRole->permissions()->detach(Permission::where('key', 'send_documents')->value('id'));
    expect($this->actor->hasAccess('send_documents', $this->supply->id))->toBeTrue();
    expect($this->actor->hasAccess('send_documents', $this->sao->id))->toBeFalse();
    $form->set('document_type_id', (string) $this->type->id)->set('subject', 'Not permitted')
        ->call('submitDocument', 'Draft')->assertHasErrors('document_from_id');
});

it('rejects an unrelated office in both list filtering and document creation', function () {
    Livewire::test(ListDocuments::class, ['mode' => 'received'])->set('officeFilter', (string) $this->other->id)->assertForbidden();
    Livewire::test(CreateDocument::class)->set('document_from_id', (string) $this->other->id)->assertHasErrors('document_from_id');
});


it('retains the issuing office when a draft is reopened', function () {
    Livewire::test(CreateDocument::class)->set('document_from_id', (string) $this->supply->id)
        ->set('document_type_id', (string) $this->type->id)->set('subject', 'Supply draft')
        ->call('submitDocument', 'Draft')->assertHasNoErrors();
    $draft = Document::where('subject', 'Supply draft')->firstOrFail();
    Livewire::test(CreateDocument::class, ['draft_id' => $draft->id])
        ->assertSet('document_from_id', $this->supply->id)
        ->set('content', 'Ready')->call('submitDocument', 'send')->assertHasNoErrors();
    expect($draft->fresh()->from_id)->toBe($this->supply->id);
    expect($draft->fresh()->document_number)->toStartWith('SUPPLY(ADMIN)-MOM-');
});


it('does not add office labels or filters for a user managing only one office', function () {
    $this->supply->update(['head_id' => null]);
    $this->actor->update(['office_id' => $this->supply->id]);
    expect($this->actor->workflowOfficeIds())->toHaveCount(2);
    expect($this->actor->managesMultipleOffices())->toBeFalse();
    ($this->makeDocument)($this->sao, 'Ordinary received');
    ($this->makeDocument)($this->other, 'Ordinary sent', ['from_id' => $this->supply->id]);
    foreach (['received', 'Sent'] as $mode) {
        Livewire::test(ListDocuments::class, ['mode' => $mode])
            ->assertSet('officeFilter', '')->assertDontSee('wire:model.live="officeFilter"', false)
            ->assertDontSee('Issuing office:')->assertDontSee('Receiving office:');
    }
});
