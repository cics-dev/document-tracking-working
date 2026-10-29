<?php

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Services\DocumentPreviewDataService;
use App\Support\DocumentPosition;

it('uses role descriptions for current document titles and keeps OIC titles', function () {
    $role = Role::create(['role' => 'vice-president', 'description' => 'Vice President']);
    $office = Office::create(['name' => 'Academic Affairs', 'abbreviation' => 'AA']);
    $head = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id, 'position' => 'vice-president']);
    $office->update(['head_id' => $head->id]);
    expect($head->documentPosition())->toBe('Vice President');
    expect($office->fresh()->workflowAssigneePosition())->toBe('Vice President');
    $office->update(['acting_head_id' => $head->id]);
    expect($office->fresh()->workflowAssigneePosition())->toBe('Officer-in-Charge, Academic Affairs');
});

it('translates legacy role-key snapshots without rewriting historical custom titles', function () {
    Role::create(['role' => 'vice-president', 'description' => 'Vice President']);
    expect(DocumentPosition::display('vice-president', null))->toBe('Vice President');
    expect(DocumentPosition::display('Dean, College of Science', null))->toBe('Dean, College of Science');
    expect(DocumentPosition::display('Officer-in-Charge, Academic Affairs', null))->toBe('Officer-in-Charge, Academic Affairs');
});

it('uses descriptions for sender recipient and signatory titles in saved-document PDFs', function () {
    $role = Role::create(['role' => 'vice-president', 'description' => 'Vice President']);
    $office = Office::create(['name' => 'Academic Affairs', 'abbreviation' => 'AA']);
    $user = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id, 'position' => 'vice-president']);
    $office->update(['head_id' => $user->id]);
    $type = DocumentType::create(['name' => 'Letter', 'abbreviation' => 'LTR']);
    $document = Document::create([
        'document_number' => 'TITLE-1', 'from_id' => $office->id, 'to_id' => $office->id,
        'from_name' => $user->name, 'from_position' => 'vice-president', 'to_position' => 'vice-president',
        'document_type_id' => $type->id, 'subject' => 'Title', 'content' => '<p>Body</p>', 'created_by' => $user->id,
    ]);
    $document->steps()->create([
        'office_id' => $office->id, 'user_id' => $user->id, 'step_type' => 'signatory', 'step_label' => 'Approved by',
        'signatory_name' => $user->name, 'signatory_position' => 'vice-president', 'sequence' => 1,
    ]);
    $data = app(DocumentPreviewDataService::class)->build($document);
    expect($data['fromPosition'])->toBe('Vice President, Academic Affairs');
    expect($data['toPosition'])->toBe('Vice President, Academic Affairs');
    expect(json_decode($data['signatories'], true)[0]['position'])->toBe('Vice President');
    expect($document->fresh()->from_position)->toBe('vice-president');
});
