<?php

use App\Models\Document;
use App\Models\DocumentCarbonCopy;
use App\Models\DocumentLog;
use App\Models\DocumentRouting;
use App\Models\DocumentSignatory;
use App\Models\DocumentType;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('an administrator receives global dashboard metrics and all recent document activity', function () {
    $data = dashboardTestData();

    $response = $this->actingAs($data['admin'])->get('/dashboard');

    $response
        ->assertOk()
        ->assertViewHas('isAdmin', true)
        ->assertViewHas('totalDocsCount', 7)
        ->assertViewHas('extDocsCount', 2)
        ->assertViewHas('grandTotalDocs', 9)
        ->assertViewHas('sentDocsCount', 3)
        ->assertViewHas('approvedDocsCount', 2)
        ->assertViewHas('rejectedDocsCount', 1)
        ->assertViewHas('draftDocsCount', 1)
        ->assertViewHas('usersCount', 3)
        ->assertViewHas('officesCount', 3)
        ->assertViewHas('docTypeTotal', 7)
        ->assertViewHas('historyLogs', function (Collection $historyLogs) use ($data): bool {
            return $historyLogs->pluck('id')->sort()->values()->all() === [
                $data['regularLog']->id,
                $data['otherVisibleLog']->id,
                $data['unrelatedLog']->id,
            ];
        })
        ->assertViewHas('topOffices', function (Collection $topOffices) use ($data): bool {
            return $topOffices->pluck('id')->sort()->values()->all() === [
                $data['officeA']->id,
                $data['officeB']->id,
                $data['officeC']->id,
            ];
        });

    expect($response->viewData('docTypes')->sum('documents_count'))->toBe(7);
    expect($response->viewData('yearlyChartData')['Documents'][(string) now()->year])->toBe(9);

    $response
        ->assertSee($data['regular']->name)
        ->assertSee($data['outsider']->name);
});

test('a regular user receives only document data they are associated with and only their activity history', function () {
    $data = dashboardTestData();

    // The position is intentionally Administrator. The dashboard must use the
    // role record, not an editable position label, to determine admin access.
    expect($data['regular']->position)->toBe('Administrator');

    $response = $this->actingAs($data['regular'])->get('/dashboard');

    $response
        ->assertOk()
        ->assertViewHas('isAdmin', false)
        ->assertViewHas('totalDocsCount', 6)
        ->assertViewHas('extDocsCount', 1)
        ->assertViewHas('grandTotalDocs', 7)
        ->assertViewHas('sentDocsCount', 2)
        ->assertViewHas('approvedDocsCount', 2)
        ->assertViewHas('rejectedDocsCount', 1)
        ->assertViewHas('draftDocsCount', 1)
        ->assertViewHas('usersCount', 1)
        ->assertViewHas('officesCount', 1)
        ->assertViewHas('activityCount', 1)
        ->assertViewHas('docTypeTotal', 6)
        ->assertViewHas('historyLogs', function (Collection $historyLogs) use ($data): bool {
            return $historyLogs->pluck('id')->all() === [$data['regularLog']->id]
                && $historyLogs->every(fn (DocumentLog $log): bool => $log->user_id === $data['regular']->id);
        })
        ->assertViewHas('topOffices', fn (Collection $topOffices): bool => $topOffices->isEmpty());

    $documentTypes = $response->viewData('docTypes');

    expect($documentTypes->sum('documents_count'))->toBe(6)
        ->and($documentTypes->firstWhere('id', $data['generalType']->id)->documents_count)->toBe(6)
        ->and($documentTypes->pluck('id'))->toContain($data['generalType']->id)
        ->not->toContain($data['privateType']->id)
        ->and($response->viewData('yearlyChartData')['Documents'][(string) now()->year])->toBe(7);

    $response
        ->assertSee('My Document Dashboard')
        ->assertSee('My Document History')
        ->assertSee($data['regular']->name)
        ->assertDontSee($data['outsider']->name);
});

/**
 * Create a data set which exercises every document relationship that makes a
 * record visible on a regular user's dashboard.
 *
 * @return array<string, mixed>
 */
function dashboardTestData(): array
{
    $adminRole = Role::query()->create([
        'role' => 'admin',
        'description' => 'Administrator',
    ]);
    $regularRole = Role::query()->create([
        'role' => 'staff',
        'description' => 'Staff',
    ]);

    $officeA = dashboardOffice('Office A', 'OFA');
    $officeB = dashboardOffice('Office B', 'OFB');
    $officeC = dashboardOffice('Office C', 'OFC');

    // Deliberately give the admin a non-admin position and the regular user an
    // Administrator position to prove the role relation is the authority.
    $admin = dashboardUser('Admin User', $adminRole, null, 'Staff');
    $regular = dashboardUser('Regular User', $regularRole, $officeA, 'Administrator');
    $outsider = dashboardUser('Other User', $regularRole, $officeB, 'Staff');

    $generalType = DocumentType::query()->create([
        'name' => 'General',
        'abbreviation' => 'GEN',
    ]);
    $privateType = DocumentType::query()->create([
        'name' => 'Private',
        'abbreviation' => 'PVT',
    ]);

    // Each of these six is visible to the regular user for a distinct reason:
    // created_by, office sent, office received, routing, signatory, and CC.
    dashboardDocument('DOC-AUTHORED', $officeB, $officeB, $generalType, $regular, 'Draft');
    dashboardDocument('DOC-OFFICE-SENT', $officeA, $officeB, $generalType, $outsider, 'Sent');
    $officeReceivedDocument = dashboardDocument('DOC-OFFICE-RECEIVED', $officeB, $officeA, $generalType, $outsider, 'Approved');

    $routedDocument = dashboardDocument('DOC-ROUTED', $officeB, $officeB, $generalType, $outsider, 'Sent');
    DocumentRouting::query()->create([
        'document_id' => $routedDocument->id,
        'user_id' => $regular->id,
        'status' => 'pending',
    ]);

    $signatoryDocument = dashboardDocument('DOC-SIGNATORY', $officeB, $officeB, $generalType, $outsider, 'Approved');
    DocumentSignatory::query()->create([
        'document_id' => $signatoryDocument->id,
        'user_id' => $regular->id,
        'signatory_label' => 'Reviewer',
        'status' => 'pending',
        'sequence' => 1,
    ]);

    $carbonCopyDocument = dashboardDocument('DOC-CARBON-COPY', $officeB, $officeB, $generalType, $outsider, 'Rejected');
    DocumentCarbonCopy::query()->create([
        'document_id' => $carbonCopyDocument->id,
        'user_id' => $regular->id,
    ]);

    // This document and its Office C must remain absent from regular-user data.
    $unrelatedDocument = dashboardDocument('DOC-PRIVATE', $officeC, $officeC, $privateType, $outsider, 'Sent');

    dashboardExternalDocument('EXT-OFFICE-A', $officeA);
    dashboardExternalDocument('EXT-OFFICE-C', $officeC);

    $regularLog = DocumentLog::query()->create([
        'document_id' => $routedDocument->id,
        'user_id' => $regular->id,
        'action' => 'viewed',
        'description' => 'Viewed assigned document',
    ]);
    // The document is visible through Office A, but this other user's action is
    // not part of the regular user's dashboard history.
    $otherVisibleLog = DocumentLog::query()->create([
        'document_id' => $officeReceivedDocument->id,
        'user_id' => $outsider->id,
        'action' => 'sent',
        'description' => 'Sent to Office A',
    ]);
    $unrelatedLog = DocumentLog::query()->create([
        'document_id' => $unrelatedDocument->id,
        'user_id' => $outsider->id,
        'action' => 'sent',
        'description' => 'Unrelated Office C activity',
    ]);

    return compact(
        'admin',
        'regular',
        'outsider',
        'officeA',
        'officeB',
        'officeC',
        'generalType',
        'privateType',
        'regularLog',
        'otherVisibleLog',
        'unrelatedLog',
    );
}

function dashboardOffice(string $name, string $abbreviation): Office
{
    return Office::query()->create([
        'name' => $name,
        'abbreviation' => $abbreviation,
        'office_type' => 'ADMIN',
    ]);
}

function dashboardUser(string $name, Role $role, ?Office $office, string $position): User
{
    return User::query()->create([
        'name' => $name,
        'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
        'password' => 'password',
        'role_id' => $role->id,
        'office_id' => $office?->id,
        'position' => $position,
    ]);
}

function dashboardDocument(
    string $documentNumber,
    Office $from,
    Office $to,
    DocumentType $type,
    User $createdBy,
    string $status,
): Document {
    return Document::query()->create([
        'document_number' => $documentNumber,
        'from_id' => $from->id,
        'to_id' => $to->id,
        'document_type_id' => $type->id,
        'subject' => $documentNumber.' subject',
        'content' => 'Dashboard test document',
        'created_by' => $createdBy->id,
        'status' => $status,
        'date_sent' => now()->toDateString(),
        'document_level' => 'Inter',
    ]);
}

function dashboardExternalDocument(string $documentNumber, Office $office): ExternalDocument
{
    return ExternalDocument::query()->create([
        'document_number' => $documentNumber,
        'from' => 'External Sender',
        'to_id' => $office->id,
        'subject' => $documentNumber.' subject',
        'received_date' => now()->toDateString(),
        'file_url' => 'documents/'.strtolower($documentNumber).'.pdf',
        'file_type' => 'application/pdf',
    ]);
}
