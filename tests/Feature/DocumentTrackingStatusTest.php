<?php

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

function trackingDocument(): array
{
    $office = Office::create([
        'name' => 'Records Office',
        'abbreviation' => 'RO',
        'office_type' => 'ADMIN',
    ]);
    $role = Role::create(['role' => 'Sender', 'description' => 'Can send documents']);
    $role->permissions()->attach(Permission::firstOrCreate([
        'key' => 'send_documents',
    ], [
        'label' => 'Send Documents',
    ]));
    $user = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id]);
    $type = DocumentType::create(['name' => 'Memorandum', 'abbreviation' => 'MEMO']);
    $document = Document::create([
        'document_number' => 'MEMO-TRACK-1',
        'from_id' => $office->id,
        'to_id' => $office->id,
        'document_type_id' => $type->id,
        'subject' => 'Tracking status',
        'content' => 'Test',
        'created_by' => $user->id,
        'status' => 'Sent',
    ]);

    return [$user, $document];
}

test('tracking status requires authentication', function () {
    [, $document] = trackingDocument();

    $this->getJson(route('documents.tracking-status', $document))
        ->assertUnauthorized();
});

test('tracking status returns case insensitive status dates without changing its response contract', function () {
    [$user, $document] = trackingDocument();
    $sentAt = now()->subMinute()->startOfSecond();

    $log = DocumentLog::create([
        'document_id' => $document->id,
        'user_id' => $user->id,
        'action' => 'Sent',
        'description' => 'Document sent',
    ]);
    $log->forceFill(['created_at' => $sentAt, 'updated_at' => $sentAt])->save();

    $this->actingAs($user)
        ->getJson(route('documents.tracking-status', $document))
        ->assertOk()
        ->assertJsonPath('statusDates.Sent', $sentAt->format('M d, h:i A'))
        ->assertJsonPath('timeline.1.title', 'Document Sent');
});
