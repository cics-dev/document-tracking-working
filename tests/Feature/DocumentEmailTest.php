<?php

use App\Mail\{DocumentForReview, DocumentStatusUpdate};
use App\Models\{Document, DocumentStep, DocumentType, Office, User};
use App\Services\{DocumentEmailService, DocumentWorkflowService};
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    config(['document-notifications.email_enabled' => true]);
    $office = function ($name) {
        $office = Office::create(['name' => $name, 'abbreviation' => $name]);
        $head = User::factory()->create(['office_id' => $office->id]);
        $oic = User::factory()->create(['office_id' => $office->id]);
        $office->update(['head_id' => $head->id, 'acting_head_id' => $oic->id]);
        return $office->fresh();
    };
    $this->owner = $office('Owner');
    $this->linkedOwner = $office('Linked');
    $this->reviewer = $office('Reviewer');
    $this->approver = $office('Approver');
    $type = DocumentType::create(['name' => 'Memo', 'abbreviation' => 'MEM', 'allow_oic_signature' => true]);
    $this->makeDocument = fn ($office, $number) => Document::create([
        'document_number' => $number, 'document_type_id' => $type->id, 'from_id' => $office->id,
        'created_by' => $office->head_id, 'subject' => 'Email test', 'content' => 'Body', 'status' => 'Sent',
    ]);
    $this->document = ($this->makeDocument)($this->owner, 'MEM-1');
    $linked = ($this->makeDocument)($this->linkedOwner, 'LINKED-1');
    foreach (range(1, 2) as $i) $this->document->attachments()->create([
        'attachment_document_id' => $linked->id, 'is_upload' => false, 'name' => 'Linked', 'file_type' => 'pdf',
    ]);
    $this->document->attachments()->create([
        'attachment_document_id' => $linked->id, 'is_upload' => true, 'name' => 'Uploaded', 'file_type' => 'pdf',
    ]);
    foreach ([[$this->reviewer, 'routing'], [$this->approver, 'signatory']] as $i => [$office, $kind]) {
        $this->document->steps()->create([
            'office_id' => $office->id, 'user_id' => $office->acting_head_id, 'assigned_user_id' => $office->head_id,
            'step_type' => $kind, 'step_label' => 'Step '.$i, 'sequence' => $i + 1, 'status' => 'Pending',
        ]);
    }
});

it('keeps email disabled by default and sends nothing when disabled', function () {
    $config = require base_path('config/document-notifications.php');
    expect($config['email_enabled'])->toBeFalse();
    config(['document-notifications.email_enabled' => false]);
    app(DocumentEmailService::class)->sent($this->document);
    app(DocumentWorkflowService::class)->approve($this->document, $this->reviewer->actingHead);
    Mail::assertNothingSent();
});

it('notifies both the first office head and OIC on sending', function () {
    app(DocumentEmailService::class)->sent($this->document);
    Mail::assertSent(DocumentForReview::class, 2);
    foreach ([$this->reviewer->head, $this->reviewer->actingHead] as $user) {
        Mail::assertSent(DocumentForReview::class, fn ($mail) => $mail->hasTo($user->email));
    }
    Mail::assertNotSent(DocumentStatusUpdate::class);
});

it('notifies the next office and owner after review without notifying attachment owners', function () {
    app(DocumentWorkflowService::class)->approve($this->document, $this->reviewer->actingHead);
    Mail::assertSent(DocumentForReview::class, 2);
    Mail::assertSent(DocumentStatusUpdate::class, 2);
    foreach ([$this->approver->head, $this->approver->actingHead] as $user) {
        Mail::assertSent(DocumentForReview::class, fn ($mail) => $mail->hasTo($user->email));
    }
    foreach ([$this->owner->head, $this->owner->actingHead] as $user) {
        Mail::assertSent(DocumentStatusUpdate::class, fn ($mail) => $mail->hasTo($user->email) && $mail->action === 'reviewed');
    }
});

it('notifies document and linked owners once each for final approval rejection and return', function ($action) {
    if ($action !== 'returned') {
        $this->document->steps()->where('step_type', 'routing')->update(['status' => 'Reviewed', 'processed_at' => now()]);
    }
    $actor = $action === 'returned' ? $this->reviewer->actingHead : $this->approver->actingHead;
    $service = app(DocumentWorkflowService::class);
    $action === 'approved' ? $service->approve($this->document, $actor) : $service->reject($this->document, $actor, 'Reason');
    Mail::assertSent(DocumentStatusUpdate::class, 4);
    foreach ([$this->owner->head, $this->owner->actingHead, $this->linkedOwner->head, $this->linkedOwner->actingHead] as $user) {
        Mail::assertSent(DocumentStatusUpdate::class, fn ($mail) => $mail->hasTo($user->email) && $mail->action === $action && $mail->document->id === $this->document->id);
    }
    Mail::assertNotSent(DocumentForReview::class);
})->with(['approved', 'rejected', 'returned']);

it('does not notify owners of uploaded attachments', function () {
    $this->document->attachments()->where('is_upload', false)->delete();
    app(DocumentWorkflowService::class)->reject($this->document, $this->reviewer->actingHead, 'Reason');
    Mail::assertSent(DocumentStatusUpdate::class, 2);
});

it('deduplicates owners who share the same email and excludes inactive recipients', function () {
    $this->owner->actingHead->update(['email' => mb_strtoupper($this->owner->head->email)]);
    $this->linkedOwner->actingHead->delete();
    app(DocumentWorkflowService::class)->reject($this->document, $this->reviewer->actingHead, 'Reason');
    Mail::assertSent(DocumentStatusUpdate::class, 2);
});

it('skips failed first-step emails and still attempts the other recipient', function () {
    $attempts = 0;
    Mail::shouldReceive('to')->twice()->andReturnUsing(function () use (&$attempts) {
        $attempts++;
        throw new RuntimeException('Mail transport unavailable');
    });
    app(DocumentEmailService::class)->sent($this->document);
    expect($attempts)->toBe(2)->and($this->document->fresh()->status)->toBe('Sent');
});

it('preserves workflow results when every email fails', function ($action) {
    $attempts = 0;
    Mail::shouldReceive('to')->times(4)->andReturnUsing(function () use (&$attempts) {
        $attempts++;
        throw new RuntimeException('Mail transport unavailable');
    });
    if ($action === 'approved') {
        $this->document->steps()->where('step_type', 'routing')->update(['status' => 'Reviewed', 'processed_at' => now()]);
        $result = app(DocumentWorkflowService::class)->approve($this->document, $this->approver->actingHead);
    } elseif ($action === 'reviewed') {
        $result = app(DocumentWorkflowService::class)->approve($this->document, $this->reviewer->actingHead);
    } else {
        $result = app(DocumentWorkflowService::class)->reject($this->document, $this->reviewer->actingHead, 'Reason');
    }
    $expected = match ($action) { 'approved' => 'Approved', 'reviewed' => 'In Process', default => 'Returned' };
    expect($this->document->fresh()->status)->toBe($expected)
        ->and($result['step']->fresh()->processed_at)->not->toBeNull()
        ->and($attempts)->toBe(4);
})->with(['approved', 'reviewed', 'returned']);

it('also skips recipient lookup failures', function () {
    $document = Mockery::mock(Document::class);
    $document->shouldReceive('nextPendingStep')->once()->andThrow(new RuntimeException('Lookup unavailable'));
    app(DocumentEmailService::class)->sent($document);
    Mail::assertNothingSent();
});
