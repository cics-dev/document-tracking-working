<?php

use App\Livewire\Documents\TrackDocument;
use App\Models\{Document, DocumentTrackingShare, DocumentType, Office, Permission, Role, User};
use Livewire\Livewire;

beforeEach(function () {
    $role = Role::create(['role' => 'public-tracking-owner', 'description' => 'Sender']);
    $role->permissions()->attach(Permission::where('key', 'send_documents')->firstOrFail());
    $office = Office::create(['name' => 'PRIVATE OFFICE FULL NAME', 'abbreviation' => 'HUB', 'office_type' => 'ADMIN']);
    $this->owner = User::factory()->create(['name' => 'PRIVATE PERSON NAME', 'office_id' => $office->id, 'role_id' => $role->id, 'signature' => 'private-signature.png']);
    $office->update(['head_id' => $this->owner->id]);
    $type = DocumentType::create(['name' => 'Request Letter Memorandum', 'abbreviation' => 'RLM']);
    $this->document = Document::create(['document_number' => 'HUB(ADMIN)-RLM-1-2026', 'from_id' => $office->id, 'document_type_id' => $type->id, 'created_by' => $this->owner->id, 'subject' => 'PRIVATE SUBJECT', 'content' => 'PRIVATE BODY', 'status' => 'Sent']);
    $this->document->steps()->create(['office_id' => $office->id, 'user_id' => $this->owner->id, 'step_type' => 'routing', 'step_label' => 'Reviewed by', 'sequence' => 1, 'status' => 'Reviewed', 'processed_at' => now()->subHour(), 'comments' => 'PRIVATE REMARKS', 'signature_path' => 'private-step-signature.png']);
    $this->document->steps()->create(['office_id' => $office->id, 'user_id' => $this->owner->id, 'step_type' => 'signatory', 'step_label' => 'Approved by', 'sequence' => 2, 'status' => 'Pending']);
    $this->document->logs()->create(['user_id' => $this->owner->id, 'action' => 'Viewed', 'description' => 'PRIVATE LOG DESCRIPTION']);
    $this->document->attachments()->create(['name' => 'PRIVATE ATTACHMENT', 'file_url' => 'private-attachment.pdf', 'file_type' => 'pdf', 'is_upload' => true]);
    $this->actingAs($this->owner);
    $this->tracker = Livewire::test(TrackDocument::class, ['number' => $this->document->document_number]);
});

it('creates random hashed public links and returns only the approved public fields to guests', function () {
    $this->tracker->call('createPublicShare')->assertHasNoErrors();
    $url = $this->tracker->get('publicShareUrl');
    $token = basename(parse_url($url, PHP_URL_PATH));
    expect($token)->toMatch('/^[a-f0-9]{64}$/');
    $share = DocumentTrackingShare::firstOrFail();
    expect($share->getRawOriginal('token_hash'))->toBe(hash('sha256', $token))->not->toBe($token);
    expect($share->expires_at->greaterThan(now()->addDays(6)))->toBeTrue();
    $this->tracker->call('createPublicShare');
    expect($this->tracker->get('publicShareUrl'))->not->toBe($url);
    auth()->logout();
    $response = $this->get($url)->assertOk()->assertSee('HUB(ADMIN)-RLM-1-2026')->assertSee('50% Complete')
        ->assertSee('Reviewed by')->assertSee('Approved by')->assertSee('HUB')
        ->assertDontSee('PRIVATE SUBJECT')->assertDontSee('PRIVATE BODY')->assertDontSee('PRIVATE REMARKS')
        ->assertDontSee('PRIVATE LOG DESCRIPTION')->assertDontSee('PRIVATE PERSON NAME')->assertDontSee('PRIVATE OFFICE FULL NAME')
        ->assertDontSee('PRIVATE ATTACHMENT')->assertDontSee('private-attachment.pdf')
        ->assertDontSee('private-signature.png')->assertDontSee('private-step-signature.png')
        ->assertDontSee('wire:snapshot')->assertDontSee('Document Activity Logs')
        ->assertHeader('Referrer-Policy', 'no-referrer')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
    $this->get(route('documents.track-document', $this->document->document_number))->assertRedirect(route('login'));
    $this->getJson(route('documents.tracking-status', $this->document))->assertUnauthorized();
    $this->get(route('documents.tracking-summary', $this->document))->assertRedirect(route('login'));
});

it('expires public links and rejects unknown or manipulated tokens', function () {
    $this->tracker->set('shareExpiresIn', '1')->call('createPublicShare');
    $url = $this->tracker->get('publicShareUrl');
    auth()->logout();
    $this->get($url)->assertOk();
    $this->travel(61)->minutes();
    $this->get($url)->assertNotFound();
    $this->get(route('tracking.public', str_repeat('0', 64)))->assertNotFound();
    $this->get('/tracking/'.$this->document->id)->assertNotFound();
});

it('revokes only the selected owned link immediately', function () {
    $this->tracker->call('createPublicShare');
    $url = $this->tracker->get('publicShareUrl');
    $id = $this->tracker->get('publicShareId');
    $this->tracker->call('createPublicShare');
    $otherUrl = $this->tracker->get('publicShareUrl');
    $this->tracker->call('revokePublicShare', $id)->assertHasNoErrors();
    auth()->logout();
    $this->get($url)->assertNotFound();
    $this->get($otherUrl)->assertOk();
});

it('validates expiration and rechecks authorization before creating or revoking links', function () {
    $this->tracker->set('shareExpiresIn', '999999')->call('createPublicShare')->assertHasErrors('shareExpiresIn');
    expect(DocumentTrackingShare::count())->toBe(0);
    $this->tracker->set('shareExpiresIn', '24')->call('createPublicShare');
    $id = $this->tracker->get('publicShareId');
    $coworker = User::factory()->create(['office_id' => $this->owner->office_id, 'role_id' => $this->owner->role_id]);
    $this->actingAs($coworker);
    Livewire::test(TrackDocument::class, ['number' => $this->document->document_number])->call('revokePublicShare', $id)->assertNotFound();
    expect(DocumentTrackingShare::findOrFail($id)->revoked_at)->toBeNull();
    $this->actingAs($this->owner);
    $this->owner->role->permissions()->detach();
    $this->tracker->call('createPublicShare')->assertForbidden();
    expect(DocumentTrackingShare::count())->toBe(1);
});

it('does not publicly expose a draft through an existing link', function () {
    $this->tracker->call('createPublicShare');
    $url = $this->tracker->get('publicShareUrl');
    $this->document->update(['status' => 'Draft']);
    $this->tracker->call('createPublicShare')->assertForbidden();
    auth()->logout();
    $this->get($url)->assertNotFound();
});
