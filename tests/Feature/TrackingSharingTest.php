<?php

use App\Models\{Document, DocumentType, Office, Permission, Role, User};

beforeEach(function () {
    $role = Role::create(['role' => 'tracking-share-sender', 'description' => 'Sender']);
    $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $office = Office::create(['name' => 'Tracking Office', 'abbreviation' => 'TRK', 'office_type' => 'ADMIN']);
    $this->sender = User::factory()->create(['office_id' => $office->id, 'role_id' => $role->id]);
    $office->update(['head_id' => $this->sender->id]);
    $type = DocumentType::create(['name' => 'Tracking Memo', 'abbreviation' => 'TM']);
    $this->document = Document::create(['document_number' => 'TRK-TM-1-2026', 'from_id' => $office->id, 'document_type_id' => $type->id, 'subject' => 'Tracking share example', 'content' => 'DOCUMENT BODY MUST NOT BE IN SUMMARY', 'created_by' => $this->sender->id, 'status' => 'Sent']);
    $this->url = route('documents.track-document', ['number' => $this->document->document_number]);
});

it('keeps shared tracking links private to signed-in authorized users', function () {
    $this->get($this->url)->assertRedirect(route('login'));
    $unrelated = User::factory()->create();
    $this->actingAs($unrelated)->get($this->url)->assertForbidden();
    $this->actingAs($this->sender)->get($this->url)->assertOk()
        ->assertSee('Sign-in required.')
        ->assertSee('Sharing does not grant access.')
        ->assertSee('value="'.$this->url.'"', false);
});

it('provides a canonical sharing link and a dedicated print summary', function () {
    foreach (range(1, 12) as $i) {
        $this->document->logs()->create(['user_id' => $this->sender->id, 'action' => 'Viewed', 'description' => 'Activity '.$i, 'created_at' => now()->addMinutes($i)]);
    }
    $html = $this->actingAs($this->sender)->get($this->url.'?list=Sent')->assertOk()->getContent();
    expect($html)->toContain('value="'.$this->url.'"', 'copyTrackingLink()', 'Printable tracking summary', route('documents.tracking-summary', $this->document));
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
    $xpath = new DOMXPath($dom);
    $summary = $xpath->query('//section[@aria-label="Printable tracking summary"]')->item(0)->textContent;
    $activityRows = $xpath->query('//section[@aria-label="Printable tracking summary"]/table[2]/tbody/tr/td[2]');
    $activities = [];
    foreach ($activityRows as $row) $activities[] = trim($row->textContent);
    expect($activities)->toHaveCount(10)->toContain('Activity 12', 'Activity 3')->not->toContain('Activity 1', 'Activity 2');
    expect($summary)->toContain('TRK-TM-1-2026', 'Tracking share example', 'Tracking Office', 'Activity 12', 'Activity 3')
        ->not->toContain('DOCUMENT BODY MUST NOT BE IN SUMMARY');
});

it('keeps the public share controls inside the Livewire component root', function () {
    $html = $this->actingAs($this->sender)->get($this->url)->assertOk()->getContent();
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
    $xpath = new DOMXPath($dom);
    $button = $xpath->query('//button[@*[name()="wire:click" and .="createPublicShare"]]')->item(0);

    expect($button)->not->toBeNull();
    $roots = $xpath->query('ancestor::*[@*[name()="wire:id"]]', $button);
    expect($roots->length)->toBe(1)
        ->and($roots->item(0)->tagName)->toBe('div');
});
