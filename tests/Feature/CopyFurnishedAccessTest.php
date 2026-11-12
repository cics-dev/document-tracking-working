<?php

use App\Models\{Document, DocumentType, Office, Permission, Role, User};
use App\Services\{DocumentQueryService, NotificationCountService};

beforeEach(function () {
    $role = Role::create(['role' => 'copy-recipient', 'description' => 'Copy recipient']);
    $role->permissions()->attach(Permission::where('key', 'receive_documents')->firstOrFail());
    $office = Office::create(['name' => 'Copy Office', 'abbreviation' => 'COPY']);
    $this->recipient = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id]);
    $office->update(['head_id' => $this->recipient->id]);
    $senderOffice = Office::create(['name' => 'Sending Office', 'abbreviation' => 'SEND']);
    $sender = User::factory()->create(['office_id' => $senderOffice->id]);
    $type = DocumentType::create(['name' => 'Copy Memo', 'abbreviation' => 'CM']);
    $this->document = Document::create([
        'document_number' => 'SEND-CM-1', 'from_id' => $senderOffice->id,
        'document_type_id' => $type->id, 'created_by' => $sender->id,
        'subject' => 'Restricted copy', 'content' => 'Body', 'status' => 'Sent',
    ]);
    $this->document->cfs()->create(['user_id' => $this->recipient->id]);
    $this->actingAs($this->recipient);
});

it('grants copy access only after approval across lists counts and direct endpoints', function (string $status) {
    $this->document->update(['status' => $status]);
    $allowed = $status === 'Approved';
    $service = app(DocumentQueryService::class);
    expect($service->listFor($this->recipient, 'received')->whereKey($this->document->id)->exists())->toBe($allowed)
        ->and($service->listFor($this->recipient, 'received', $this->recipient->office_id)->whereKey($this->document->id)->exists())->toBe($allowed)
        ->and($service->canView($this->recipient, $this->document->document_number))->toBe($allowed);
    $counts = app(NotificationCountService::class)->for($this->recipient);
    expect($counts['received'])->toBe($allowed ? 1 : 0)->and($counts['total'])->toBe($allowed ? 1 : 0);
    $this->get(route('documents.track-document', ['number' => $this->document->document_number]))->assertStatus($allowed ? 200 : 403);
    $this->get(route('documents.tracking-summary', $this->document))->assertStatus($allowed ? 200 : 403);
})->with(['Draft', 'Sent', 'In Process', 'Returned', 'Rejected', 'Approved']);

it('preserves access for a workflow participant who is also copy furnished', function () {
    $step = $this->document->steps()->create([
        'user_id' => $this->recipient->id, 'office_id' => $this->recipient->office_id,
        'assigned_user_id' => $this->recipient->id, 'step_type' => 'routing',
        'step_label' => 'Review', 'sequence' => 1, 'status' => 'Pending',
    ]);
    $service = app(DocumentQueryService::class);
    expect($service->canView($this->recipient, $this->document->document_number))->toBeTrue();
    $step->update(['status' => 'Returned', 'processed_at' => now()]);
    $this->document->update(['status' => 'Returned']);
    expect($service->canView($this->recipient, $this->document->document_number))->toBeTrue();
});
