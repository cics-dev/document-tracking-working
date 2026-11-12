<?php

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\DocumentType;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/dashboard')->assertStatus(200);
});

test('dashboard statistics and activity come from the database', function () {
    $office = Office::create(['name' => 'Records Office', 'abbreviation' => 'RO', 'office_type' => 'ADMIN']);
    $user = User::factory()->create(['office_id' => $office->id]);
    $role = Role::create(['role' => 'dashboard-sender', 'description' => 'Sender']);
    $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $user->update(['role_id' => $role->id]);
    $type = DocumentType::create(['name' => 'Memorandum', 'abbreviation' => 'MEMO']);
    $document = Document::create([
        'document_number' => 'MEMO-DASH-1',
        'from_id' => $office->id,
        'to_id' => $office->id,
        'document_type_id' => $type->id,
        'subject' => 'Dashboard source document',
        'content' => 'Test',
        'created_by' => $user->id,
        'status' => 'Sent',
    ]);
    ExternalDocument::create([
        'document_number' => 'EXT-DASH-1',
        'from' => 'External Agency',
        'to_id' => $office->id,
        'subject' => 'External dashboard source',
        'received_date' => now(),
        'file_url' => 'attachments/dashboard.pdf',
        'file_type' => 'pdf',
    ]);
    DocumentLog::create([
        'document_id' => $document->id,
        'user_id' => $user->id,
        'action' => 'Sent',
        'description' => 'Dashboard activity from database',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertViewHas('metrics', function (array $metrics) {
            $byLabel = collect($metrics)->keyBy('label');

            return $byLabel['Documents']['total'] === 2
                && $byLabel['Received']['total'] === 2
                && $byLabel['Users']['total'] === 1
                && $byLabel['Offices']['total'] === 1;
        })
        ->assertSee('MEMO-DASH-1')
        ->assertSee('Dashboard activity from database');
});


test('dashboard activity follows document permissions before limiting the feed', function (array $permissions, array $expected) {
    $role = Role::create(['role' => 'dashboard-viewer', 'description' => 'Viewer']);
    $role->permissions()->attach(Permission::whereIn('key', $permissions)->pluck('id'));
    $office = Office::create(['name' => 'Viewer Office', 'abbreviation' => 'VO', 'office_type' => 'ADMIN']);
    $otherOffice = Office::create(['name' => 'Other Office', 'abbreviation' => 'OO', 'office_type' => 'ADMIN']);
    $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
    $other = User::factory()->create(['office_id' => $otherOffice->id]);
    $type = DocumentType::create(['name' => 'Activity Letter', 'abbreviation' => 'AL']);
    foreach (['sent', 'received', 'private', 'blocked'] as $kind) {
        $document = Document::create([
            'document_number' => 'ACTIVITY-'.$kind,
            'from_id' => $kind === 'sent' ? $office->id : $otherOffice->id,
            'to_id' => in_array($kind, ['received', 'blocked']) ? $office->id : null,
            'document_type_id' => $type->id, 'subject' => $kind, 'content' => 'Body',
            'created_by' => $kind === 'sent' ? $user->id : $other->id, 'status' => 'Sent',
        ]);
        if ($kind === 'blocked') {
            $document->steps()->create(['user_id' => $other->id, 'office_id' => $otherOffice->id, 'step_type' => 'routing', 'step_label' => 'Earlier review', 'sequence' => 1, 'status' => 'Pending']);
            $document->steps()->create(['user_id' => $user->id, 'office_id' => $office->id, 'step_type' => 'routing', 'step_label' => 'Later review', 'sequence' => 2, 'status' => 'Pending']);
        }
        // More than eight hidden entries must not crowd accessible history out.
        foreach (range(1, $kind === 'private' ? 10 : 1) as $index) {
            $document->logs()->create(['user_id' => $other->id, 'action' => 'Sent', 'description' => 'DETAIL-'.$kind]);
        }
    }
    $response = $this->actingAs($user)->get('/dashboard')->assertOk();
    foreach (['sent', 'received', 'private', 'blocked'] as $kind) {
        if (in_array($kind, $expected)) {
            $response->assertSee('ACTIVITY-'.$kind)->assertSee('DETAIL-'.$kind);
        } else {
            $response->assertDontSee('ACTIVITY-'.$kind)->assertDontSee('DETAIL-'.$kind);
        }
    }
})->with([
    'sender' => [['send_documents'], ['sent']],
    'receiver' => [['receive_documents'], ['received']],
    'sender and receiver' => [['send_documents', 'receive_documents'], ['sent', 'received']],
    'no document permissions' => [[], []],
]);

test('users with global document permission retain global dashboard history', function () {
    $role = Role::create(['role' => 'global-dashboard-viewer', 'description' => 'Global viewer']);
    $role->permissions()->attach(Permission::where('key', 'view_all_documents')->firstOrFail());
    $user = User::factory()->create(['role_id' => $role->id]);
    $other = User::factory()->create();
    $office = Office::create(['name' => 'Private Office', 'abbreviation' => 'PO', 'office_type' => 'ADMIN']);
    $type = DocumentType::create(['name' => 'Global Letter', 'abbreviation' => 'GL']);
    $document = Document::create(['document_number' => 'GLOBAL-HISTORY', 'from_id' => $office->id, 'document_type_id' => $type->id, 'subject' => 'Global', 'content' => 'Body', 'created_by' => $other->id, 'status' => 'Sent']);
    $document->logs()->create(['user_id' => $other->id, 'action' => 'Sent', 'description' => 'Global history description']);
    $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('GLOBAL-HISTORY')->assertSee('Global history description');
});
