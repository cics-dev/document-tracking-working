<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\DocumentType;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        $documents = Document::query();
        $externalDocuments = ExternalDocument::query();

        if (! $isAdmin) {
            $this->scopeDocumentsForUser($documents, $user);

            if ($user->office_id) {
                $externalDocuments->where('to_id', $user->office_id);
            } else {
                // An account without an office cannot have office-delivered
                // external documents.
                $externalDocuments->whereRaw('1 = 0');
            }
        }

        $totalDocsCount = (clone $documents)->count();
        $extDocsCount = (clone $externalDocuments)->count();
        $grandTotalDocs = $totalDocsCount + $extDocsCount;

        // Existing records use both title and lower case status values, so
        // normalize them while calculating the dashboard metrics.
        $sentDocsCount = $this->countDocumentsByStatus($documents, 'sent');
        $approvedDocsCount = $this->countDocumentsByStatus($documents, 'approved');
        $rejectedDocsCount = $this->countDocumentsByStatus($documents, 'rejected');
        $draftDocsCount = $this->countDocumentsByStatus($documents, 'draft');

        // Organization totals are intentionally never queried for regular
        // users. Their dashboard only identifies their own account and office.
        $officesCount = $isAdmin ? Office::count() : ($user->office_id ? 1 : 0);
        $usersCount = $isAdmin ? User::count() : 1;
        $officeName = $user->office?->name;

        $statusTotal = max(1, $totalDocsCount);
        $approvedPct = round(($approvedDocsCount / $statusTotal) * 100, 1);
        $sentPct = round(($sentDocsCount / $statusTotal) * 100, 1);
        $draftPct = round(($draftDocsCount / $statusTotal) * 100, 1);
        $rejectedPct = round(($rejectedDocsCount / $statusTotal) * 100, 1);

        $docTypesQuery = DocumentType::query()->withCount([
            'documents as documents_count' => function (Builder $query) use ($isAdmin, $user): void {
                if (! $isAdmin) {
                    $this->scopeDocumentsForUser($query, $user);
                }
            },
        ]);

        if (! $isAdmin) {
            // Do not send type names that have no records in this user's
            // dashboard data set.
            $docTypesQuery->whereHas('documents', function (Builder $query) use ($user): void {
                $this->scopeDocumentsForUser($query, $user);
            });
        }

        $docTypes = $docTypesQuery->get();
        $docTypeTotal = max(1, $docTypes->sum('documents_count'));

        $topOffices = collect();

        if ($isAdmin) {
            $topOffices = Office::withCount(['sentDocuments', 'receivedDocuments'])
                ->get()
                ->map(function (Office $office) {
                    $office->total_activity = $office->sent_documents_count + $office->received_documents_count;

                    return $office;
                })
                ->sortByDesc('total_activity')
                ->take(4)
                ->values();
        }

        $history = DocumentLog::with(['document', 'user'])
            ->whereRaw('LOWER(action) IN (?, ?)', ['viewed', 'sent']);

        if (! $isAdmin) {
            // A regular user can see only actions they performed, and only on
            // documents included in their dashboard data set.
            $history
                ->where('user_id', $user->id)
                ->whereIn('document_id', (clone $documents)->select('documents.id'));
        }

        $activityCount = $isAdmin ? 0 : (clone $history)->count();
        $historyLogs = $history->latest()->take(7)->get();

        $currentYear = (int) date('Y');
        $startYear = 2025;
        $yearlyDocsMap = (clone $documents)->whereNotNull('created_at')
            ->get(['created_at'])
            ->groupBy(fn (Document $document) => $document->created_at->format('Y'))
            ->map(fn ($group) => $group->count());
        $yearlyExternalDocsMap = (clone $externalDocuments)->whereNotNull('created_at')
            ->get(['created_at'])
            ->groupBy(fn (ExternalDocument $document) => $document->created_at->format('Y'))
            ->map(fn ($group) => $group->count());

        $yearlyChartData = [
            'Documents' => [],
        ];

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $yearString = (string) $year;
            $yearlyChartData['Documents'][$yearString] = $yearlyDocsMap->get($yearString, 0)
                + $yearlyExternalDocsMap->get($yearString, 0);
        }

        return view('dashboard', compact(
            'totalDocsCount',
            'extDocsCount',
            'grandTotalDocs',
            'sentDocsCount',
            'approvedDocsCount',
            'rejectedDocsCount',
            'draftDocsCount',
            'officesCount',
            'usersCount',
            'statusTotal',
            'approvedPct',
            'sentPct',
            'draftPct',
            'rejectedPct',
            'docTypes',
            'docTypeTotal',
            'topOffices',
            'historyLogs',
            'yearlyChartData',
            'currentYear',
            'startYear',
            'isAdmin',
            'activityCount',
            'officeName'
        ));
    }

    /**
     * Limit a dashboard document query to records the user is involved with.
     *
     * This intentionally mirrors the relationships used by the document list:
     * documents created or sent/received by the user's office, plus records
     * assigned to the user for routing, signing, or carbon-copy review.
     */
    private function scopeDocumentsForUser(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->where('created_by', $user->id)
                ->orWhereHas('routings', fn (Builder $routing) => $routing->where('user_id', $user->id))
                ->orWhereHas('signatories', fn (Builder $signatory) => $signatory->where('user_id', $user->id))
                ->orWhereHas('cfs', fn (Builder $carbonCopy) => $carbonCopy->where('user_id', $user->id));

            if ($user->office_id) {
                $query
                    ->orWhere('from_id', $user->office_id)
                    ->orWhere('to_id', $user->office_id);
            }
        });
    }

    private function countDocumentsByStatus(Builder $documents, string $status): int
    {
        return (clone $documents)
            ->whereRaw('LOWER(status) = ?', [$status])
            ->count();
    }
}
