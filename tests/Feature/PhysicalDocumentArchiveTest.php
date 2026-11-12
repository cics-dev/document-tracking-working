<?php

use App\Livewire\Documents\ArchivePhysicalDocument;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('a signed paper document is archived privately and appears in the selected recipients inbox', function () {
    Storage::fake('local');

    $senderRole = Role::create(['role' => 'sender', 'description' => 'Sender']);
    $senderRole->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $receiverRole = Role::create(['role' => 'receiver', 'description' => 'Receiver']);
    $receiverRole->permissions()->attach(Permission::where('key', 'receive_documents')->firstOrFail());

    $senderOffice = Office::create(['name' => 'Sender Office', 'abbreviation' => 'SO', 'office_type' => 'ADMIN', 'document_number_tag' => 'ADMIN']);
    $receiverOffice = Office::create(['name' => 'Receiver Office', 'abbreviation' => 'RO', 'office_type' => 'ADMIN']);
    $sender = User::factory()->create(['office_id' => $senderOffice->id, 'role_id' => $senderRole->id]);
    $recipient = User::factory()->create(['office_id' => $receiverOffice->id, 'role_id' => $receiverRole->id]);
    $otherUser = User::factory()->create(['office_id' => $receiverOffice->id, 'role_id' => $receiverRole->id]);

    $type = DocumentType::create([
        'name' => 'Office Memo',
        'abbreviation' => 'MEMO',
        'document_level' => 'Intra',
        'is_publicly_creatable' => true,
    ]);

    $this->actingAs($sender)->get(route('documents.archive-physical', ['level' => 'Intra']))
        ->assertOk()
        ->assertSee('Upload manual document');

    $scan = UploadedFile::fake()->createWithContent('signed.pdf', "%PDF-1.4\n%%EOF");

    Livewire::withQueryParams(['level' => 'Intra'])->test(ArchivePhysicalDocument::class)
        ->set('documentTypeId', (string) $type->id)
        ->set('is_manual_document_number', true)
        ->assertSee('SO(ADMIN)-MEMO-')
        ->set('manual_document_sequence', '1')
        ->set('manual_document_year', '2025')
        ->set('subject', 'Urgent paper memo')
        ->set('scan', $scan)
        ->set('selectedRecipient', (string) $recipient->id)
        ->call('addRecipient')
        ->call('archive')
        ->assertHasNoErrors()
        ->assertRedirect(route('documents.view-document', ['number' => 'SO(ADMIN)-MEMO-1-2025']));

    $document = Document::where('document_number', 'SO(ADMIN)-MEMO-1-2025')->firstOrFail();
    expect($document->is_physical)->toBeTrue()
        ->and($document->status)->toBe('Archived')
        ->and($document->steps()->count())->toBe(0)
        ->and($document->cfs()->pluck('user_id')->all())->toBe([$recipient->id]);
    Storage::disk('local')->assertExists($document->file_url);

    $scanUrl = route('documents.physical-scan', $document);

    $this->actingAs($recipient)->get(route('documents.list-documents', ['mode' => 'received']))
        ->assertOk()
        ->assertSee('Urgent paper memo')
        ->assertSee('Physical');

    $this->get(route('documents.view-document', ['number' => $document->document_number]))
        ->assertOk()
        ->assertSee($scanUrl)
        ->assertSee('CF recipients');

    $this->get($scanUrl)->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $this->actingAs($otherUser)->get($scanUrl)->assertForbidden();
    $this->get(route('documents.archive-physical'))->assertForbidden();
});
