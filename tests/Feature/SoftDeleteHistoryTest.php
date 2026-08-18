<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentStep;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use App\Services\ArchivalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SoftDeleteHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_and_offices_are_archived_and_hidden_from_normal_lists(): void
    {
        $office = Office::create(['name' => 'Historical Office', 'abbreviation' => 'HO', 'office_type' => 'ADMIN']);
        $user = User::factory()->create(['office_id' => $office->id]);

        $user->delete();
        $office->delete();

        $this->assertNull(User::find($user->id));
        $this->assertNull(Office::find($office->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
        $this->assertNotNull(Office::withTrashed()->find($office->id));
    }

    public function test_archiving_a_user_clears_live_office_assignments_and_can_be_restored(): void
    {
        $office = Office::create(['name' => 'Records', 'abbreviation' => 'REC', 'office_type' => 'ADMIN']);
        $user = User::factory()->create(['office_id' => $office->id]);
        $office->update(['head_id' => $user->id, 'acting_head_id' => $user->id]);

        app(ArchivalService::class)->archiveUser($user);

        $this->assertSoftDeleted($user);
        $this->assertDatabaseHas('offices', ['id' => $office->id, 'head_id' => null, 'acting_head_id' => null]);

        app(ArchivalService::class)->restoreUser(User::onlyTrashed()->findOrFail($user->id));

        $this->assertNotNull(User::find($user->id));
    }

    public function test_a_user_with_pending_document_work_cannot_be_archived(): void
    {
        [$document, $user] = $this->documentFor($this->createOffice('Routing', 'ROU'));
        DocumentStep::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'assigned_user_id' => $user->id,
            'office_id' => $user->office_id,
            'step_type' => 'routing',
            'step_label' => 'Review',
        ]);

        $this->expectException(ValidationException::class);
        app(ArchivalService::class)->archiveUser($user);
    }

    public function test_office_archival_is_blocked_by_live_dependencies_and_empty_offices_can_be_restored(): void
    {
        $occupiedOffice = $this->createOffice('Occupied', 'OCC');
        User::factory()->create(['office_id' => $occupiedOffice->id]);

        try {
            app(ArchivalService::class)->archiveOffice($occupiedOffice);
            $this->fail('An office with active users must not be archived.');
        } catch (ValidationException) {
            $this->assertNotNull(Office::find($occupiedOffice->id));
        }

        $emptyOffice = $this->createOffice('Archive', 'ARC');
        app(ArchivalService::class)->archiveOffice($emptyOffice);
        $this->assertSoftDeleted($emptyOffice);

        app(ArchivalService::class)->restoreOffice(Office::onlyTrashed()->findOrFail($emptyOffice->id));
        $this->assertNotNull(Office::find($emptyOffice->id));
    }

    private function createOffice(string $name, string $abbreviation): Office
    {
        return Office::create(compact('name', 'abbreviation') + ['office_type' => 'ADMIN']);
    }

    private function documentFor(Office $office): array
    {
        $user = User::factory()->create(['office_id' => $office->id]);
        $type = DocumentType::create(['name' => 'Test Type', 'abbreviation' => 'TT']);
        $document = Document::create([
            'from_id' => $office->id,
            'document_type_id' => $type->id,
            'subject' => 'Archive test',
            'content' => 'Test content',
            'created_by' => $user->id,
            'status' => 'Sent',
        ]);

        return [$document, $user];
    }
}
