<?php

use App\Livewire\Documents\ListDocuments;
use App\Models\{Document, DocumentType, Office, Permission, Role, User};
use Livewire\Livewire;

beforeEach(function () {
    $role = Role::create(['role' => 'list-order-viewer', 'description' => 'Viewer']);
    $role->permissions()->attach(Permission::whereIn('key', ['send_documents', 'receive_documents', 'view_all_documents'])->pluck('id'));
    $this->office = Office::create(['name' => 'Viewer Office', 'abbreviation' => 'VO', 'office_type' => 'ADMIN']);
    $this->user = User::factory()->create(['role_id' => $role->id, 'office_id' => $this->office->id]);
    $this->office->update(['head_id' => $this->user->id]);
    $this->type = DocumentType::create(['name' => 'Order Letter', 'abbreviation' => 'OL']);
    $this->makeDocument = function (string $subject, string $sentDate, array $attributes = []) {
        return Document::create(array_merge([
            'document_number' => 'ORDER-'.Document::count(), 'subject' => $subject, 'content' => 'Body',
            'document_type_id' => $this->type->id, 'created_by' => $this->user->id,
            'from_id' => $this->office->id, 'to_id' => $this->office->id, 'status' => 'Sent',
            'date_sent' => $sentDate, 'created_at' => '2026-10-01 08:00:00',
        ], $attributes));
    };
    $this->actingAs($this->user);
});

it('keeps drafts in Sent only and orders circulating documents by sent date with stable ties', function (string $mode) {
    $old = ($this->makeDocument)('Old sent', '2026-10-01', ['updated_at' => now()]);
    $new = ($this->makeDocument)('New sent', '2026-10-03', ['updated_at' => '2026-10-03']);
    $tie = ($this->makeDocument)('Same date sent', '2026-10-03');
    $draft = ($this->makeDocument)('Draft row', '2026-10-05', ['status' => 'Draft', 'document_number' => null]);
    $expectedIds = $mode === 'Sent' ? [$draft->id, $tie->id, $new->id, $old->id] : [$tie->id, $new->id, $old->id];
    $component = Livewire::test(ListDocuments::class, ['mode' => $mode])
        ->assertViewHas('documents', fn ($rows) => $rows->pluck('id')->all() === $expectedIds)
        ->assertSeeInOrder(['Same date sent', 'New sent', 'Old sent'])
        ->assertSee(count($expectedIds).' documents')->assertDontSee('scope="rowgroup"', false);
    if ($mode === 'all') {
        $component->assertDontSee('Draft row')->assertDontSee('value="Draft"', false)
            ->set('search', 'Draft row')->assertSee('0 documents')->assertViewHas('documents', fn ($rows) => $rows->total() === 0)
            ->set('search', '')->set('statusFilter', 'Draft')->assertSee('0 documents')
            ->set('statusFilter', '');
    } else {
        $component->assertSee('Draft row');
    }
    $old->accessLogs()->create(['user_id' => $this->user->id, 'action' => 'Viewed']);
    $old->update(['updated_at' => now()->addHour()]);
    $component->call('$refresh')->assertViewHas('documents', fn ($rows) => $rows->pluck('id')->all() === $expectedIds);
})->with(['Sent', 'all']);

it('puts the next assigned action first including approved generation work', function () {
    $oldAction = ($this->makeDocument)('Older needs action', '2026-10-01');
    $oldAction->steps()->create(['office_id' => $this->office->id, 'user_id' => $this->user->id, 'step_type' => 'routing', 'step_label' => 'Review', 'sequence' => 1, 'status' => 'Pending']);
    $generation = ($this->makeDocument)('Generate next', '2026-10-02', ['status' => 'Approved']);
    $generation->steps()->create(['office_id' => $this->office->id, 'user_id' => $this->user->id, 'step_type' => 'action', 'step_label' => 'Generate', 'sequence' => 1, 'status' => 'Pending']);
    $new = ($this->makeDocument)('New informational', '2026-10-05');
    Livewire::test(ListDocuments::class, ['mode' => 'received'])
        ->assertViewHas('documents', fn ($rows) => $rows->pluck('id')->all() === [$generation->id, $oldAction->id, $new->id])
        ->assertSeeInOrder(['Generate next', 'Older needs action', 'New informational']);
});

it('does not prioritize a heads documents when an OIC must act', function () {
    $oic = User::factory()->create(['office_id' => $this->office->id]);
    $this->office->update(['acting_head_id' => $oic->id]);
    $pending = ($this->makeDocument)('Assigned to OIC', '2026-10-01');
    $pending->steps()->create(['office_id' => $this->office->id, 'user_id' => $this->user->id, 'step_type' => 'routing', 'step_label' => 'Review', 'sequence' => 1, 'status' => 'Pending']);
    $new = ($this->makeDocument)('Newest information', '2026-10-05');
    Livewire::test(ListDocuments::class, ['mode' => 'received'])
        ->assertDontSee('Needs your action')
        ->assertViewHas('documents', fn ($rows) => $rows->pluck('id')->all() === [$new->id, $pending->id]);
});

it('counts all filtered matches across pages and updates the count on search', function () {
    foreach (range(1, 12) as $i) {
        ($this->makeDocument)('Document '.$i, '2026-10-01');
    }
    Livewire::test(ListDocuments::class, ['mode' => 'Sent'])
        ->assertSee('12 documents')
        ->assertViewHas('documents', fn ($rows) => $rows->count() === 10 && $rows->total() === 12)
        ->set('search', 'Document 12')->assertSee('1 document')->assertDontSee('12 documents')
        ->set('search', 'No match')->assertSee('0 documents')->assertSee('No documents found');
});

it('does not prioritize a later pending step or completed work', function () {
    $otherOffice = Office::create(['name' => 'Earlier Office', 'abbreviation' => 'EO', 'office_type' => 'ADMIN']);
    $other = User::factory()->create(['office_id' => $otherOffice->id]);
    $otherOffice->update(['head_id' => $other->id]);
    $later = ($this->makeDocument)('Waiting on someone else', '2026-10-01');
    foreach ([[$this->user, $this->office, 'Reviewed'], [$other, $otherOffice, 'Pending'], [$this->user, $this->office, 'Pending']] as $index => [$user, $office, $status]) {
        $later->steps()->create(['office_id' => $office->id, 'user_id' => $user->id, 'step_type' => 'routing', 'step_label' => 'Review', 'sequence' => $index + 1, 'status' => $status]);
    }
    $new = ($this->makeDocument)('Newest informational document', '2026-10-05');
    Livewire::test(ListDocuments::class, ['mode' => 'received'])
        ->assertDontSee('Needs your action')
        ->assertViewHas('documents', fn ($rows) => $rows->pluck('id')->all() === [$new->id, $later->id])
        ->set('statusFilter', 'Approved')->assertSee('0 documents');
});


it('filters physical and digital documents and resets pagination and filters', function (string $mode) {
    $physical = ($this->makeDocument)('Paper document', '2026-10-02', ['is_physical' => true]);
    $digital = ($this->makeDocument)('Electronic document', '2026-10-01', ['is_physical' => false]);
    Livewire::test(ListDocuments::class, ['mode' => $mode])
        ->assertSee('All formats')->assertSee('2 documents')
        ->call('setPage', 2)
        ->set('formatFilter', 'physical')
        ->assertSet('paginators.page', 1)
        ->assertViewHas('documents', fn ($rows) => $rows->pluck('id')->all() === [$physical->id])
        ->assertSee('1 document')->assertDontSee('Electronic document')
        ->set('search', 'Electronic')->assertSee('0 documents')
        ->set('search', '')->set('formatFilter', 'digital')
        ->assertViewHas('documents', fn ($rows) => $rows->pluck('id')->all() === [$digital->id])
        ->assertSee('1 document')->assertDontSee('Paper document')
        ->call('resetFilters')->assertSet('formatFilter', '')
        ->assertSee('2 documents');
})->with(['all', 'Sent', 'received']);
