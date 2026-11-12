<?php

use App\Livewire\Documents\{CreateDocument, ViewDocument};
use App\Models\{Document, DocumentType, Office, Permission, Role, User};
use Livewire\Livewire;

it('replaces the preview element and hides stale content when switching attachments', function (string $componentClass) {
    $role = Role::create(['role' => 'attachment-switcher', 'description' => 'Sender']);
    $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $office = Office::create(['name' => 'Preview Office', 'abbreviation' => 'PRE', 'office_type' => 'ADMIN']);
    $user = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id]);
    $office->update(['head_id' => $user->id]);
    $type = DocumentType::create(['name' => 'Preview memo', 'abbreviation' => 'PM', 'recipient_mode' => 'none', 'is_publicly_creatable' => true, 'allow_attachments' => true]);
    $document = Document::create(['document_number' => 'PRE-PM-1', 'document_type_id' => $type->id, 'from_id' => $office->id, 'created_by' => $user->id, 'subject' => 'Attachment switching', 'content' => 'Body', 'status' => 'Draft']);
    $first = $document->attachments()->create(['name' => 'First PDF', 'file_url' => 'attachments/first.pdf', 'file_type' => 'pdf', 'is_upload' => true]);
    $second = $document->attachments()->create(['name' => 'Second PDF', 'file_url' => 'attachments/second.pdf', 'file_type' => 'pdf', 'is_upload' => true]);
    $image = $document->attachments()->create(['name' => 'Image', 'file_url' => 'attachments/image.png', 'file_type' => 'png', 'is_upload' => true]);
    $this->actingAs($user);
    $params = $componentClass === CreateDocument::class ? ['draft_id' => $document->id] : ['number' => $document->document_number];
    $component = Livewire::test($componentClass, $params)->call('viewAttachment', $first->id, 'internal');
    $firstUrl = $component->get('attachmentPreviewUrl');
    $component->assertSee('attachment-media-'.md5('pdf|'.$firstUrl), false)->assertSee('Loading attachment...');
    $component->call('viewAttachment', $second->id, 'internal');
    $secondUrl = $component->get('attachmentPreviewUrl');
    $component->assertSee('attachment-media-'.md5('pdf|'.$secondUrl), false)
        ->assertDontSee('src="'.$firstUrl.'"', false)
        ->assertSee('wire:loading.remove', false)->assertSee('x-show="ready"', false);
    $component->call('viewAttachment', $image->id, 'internal');
    $component->assertSee('attachment-media-'.md5('image|'.$component->get('attachmentPreviewUrl')), false)
        ->assertDontSee('src="'.$secondUrl.'"', false);
})->with(['creation form' => [CreateDocument::class], 'document view' => [ViewDocument::class]]);
