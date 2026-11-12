<?php

use App\Models\{Document, DocumentType, ExternalDocument, Office, Permission, Role, User};
use App\Livewire\Documents\ListDocuments;
use Livewire\Livewire;

beforeEach(function () {
    $role = Role::create(['role' => 'sidebar-admin', 'description' => 'Administrator']);
    $role->permissions()->attach(Permission::pluck('id'));
    $office = Office::create(['name' => 'Sidebar Office', 'abbreviation' => 'SIDE', 'office_type' => 'ADMIN']);
    $this->user = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id]);
    $office->update(['head_id' => $this->user->id]);
    $type = DocumentType::create(['name' => 'Sidebar Memo', 'abbreviation' => 'SM', 'is_publicly_creatable' => true, 'recipient_mode' => 'none']);
    $this->document = Document::create(['document_number' => 'SIDE-1', 'from_id' => $office->id, 'document_type_id' => $type->id, 'subject' => 'Sidebar document', 'content' => 'Body', 'status' => 'Returned', 'created_by' => $this->user->id]);
    $this->draft = Document::create(['document_number' => null, 'from_id' => $office->id, 'document_type_id' => $type->id, 'subject' => 'Sidebar draft', 'content' => 'Body', 'status' => 'Draft', 'created_by' => $this->user->id]);
    $this->external = ExternalDocument::create(['document_number' => 'EXT-SIDE-1', 'from' => 'Agency', 'to_id' => $office->id, 'subject' => 'External', 'received_date' => now(), 'file_url' => 'attachments/test.pdf', 'file_type' => 'pdf']);
    $this->actingAs($this->user);
    $this->assertCurrentLink = function (string $html, string $href): void {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $links = (new DOMXPath($dom))->query('//a[@href="'.$href.'" and @data-current]');
        expect($links->length)->toBeGreaterThan(0);
    };
});

it('highlights each document section and retains the highlight after notification polling', function (string $path, string $section) {
    $path = strtr($path, ['{draft}' => $this->draft->id, '{external}' => $this->external->id]);
    $response = $this->get($path)->assertOk();
    $links = [
        'all' => route('documents.list-documents', 'all'), 'Sent' => route('documents.list-documents', 'Sent'),
        'received' => route('documents.list-documents', 'received'), 'write' => route('documents.create-document'),
        'external' => route('documents.list-external-documents'),
    ];
    ($this->assertCurrentLink)($response->getContent(), $links[$section]);
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
    foreach ((new DOMXPath($dom))->query('//*[@*[name()="wire:snapshot"]]') as $element) {
        $snapshot = $element->getAttribute('wire:snapshot');
        $data = json_decode($snapshot, true);
        if (($data['memo']['name'] ?? '') !== 'notifications.sidebar-notifications') continue;
        $update = $this->withHeader('X-Livewire', 'true')->postJson(route('default-livewire.update'), ['components' => [[
            'snapshot' => $snapshot, 'updates' => [], 'calls' => [['path' => '', 'method' => 'refreshNotifications', 'params' => []]],
        ]]])->assertOk();
        ($this->assertCurrentLink)($update->json('components.0.effects.html'), $links[$section]);
        $this->flushHeaders();
        return;
    }
    $this->fail('Sidebar notification component snapshot was not found.');
})->with([
    ['documents/all', 'all'], ['documents/Sent', 'Sent'], ['documents/received', 'received'],
    ['documents/create', 'write'], ['documents/edit-draft/{draft}', 'Sent'],
    ['documents/create-revision/SIDE-1', 'Sent'], ['documents/archive-physical', 'Sent'],
    ['documents/list-external-documents', 'external'], ['documents/receive-external-document', 'external'],
    ['documents/view-external-document/{external}', 'external'],
    ['documents/view/SIDE-1?list=Sent', 'Sent'], ['documents/track/SIDE-1?list=received', 'received'],
    ['documents/view/SIDE-1?list=all', 'all'],
]);

it('preserves the originating list when opening or tracking a document', function () {
    foreach (['all', 'Sent', 'received'] as $mode) {
        Livewire::test(ListDocuments::class, ['mode' => $mode])
            ->call('viewDocument', $this->document->document_number)
            ->assertRedirect(route('documents.view-document', ['number' => $this->document->document_number, 'list' => $mode]));
        Livewire::test(ListDocuments::class, ['mode' => $mode])
            ->call('trackDocument', $this->document->document_number)
            ->assertRedirect(route('documents.track-document', ['number' => $this->document->document_number, 'list' => $mode]));
    }
});


it('highlights the parent navigation for dashboard and management pages', function (string $path, string $target) {
    $path = strtr($path, ['{user}' => $this->user->id, '{office}' => $this->user->office_id]);
    $response = $this->get($path)->assertOk();
    ($this->assertCurrentLink)($response->getContent(), url($target));
})->with([
    ['dashboard', 'dashboard'], ['users', 'users'], ['users/create', 'users'], ['users/edit/{user}', 'users'],
    ['offices', 'offices'], ['offices/create', 'offices'], ['offices/edit/{office}', 'offices'],
    ['roles', 'roles'], ['access-rights', 'access-rights'],
    ['document-types', 'document-types'], ['document-flows', 'document-flows'],
]);

it('renders desktop sidebar collapse controls while retaining mobile navigation', function () {
    $this->get('/dashboard')->assertOk()
        ->assertSee('collapsible="true"', false)
        ->assertSee('data-flux-sidebar-collapse', false)
        ->assertSee('data-flux-sidebar-item', false)
        ->assertSee('Collapse or expand sidebar')
        ->assertSee('data-flux-sidebar-toggle', false);
});
