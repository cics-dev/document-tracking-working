<?php

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\DocumentType;
use App\Models\ExternalDocument;
use App\Models\Office;
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
