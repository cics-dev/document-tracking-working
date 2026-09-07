<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\DocumentType;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalDocsCount = Document::count();
        $extDocsCount = ExternalDocument::count();
        $grandTotalDocs = $totalDocsCount + $extDocsCount;

        $sentDocsCount = Document::where('status', 'Sent')->count();
        $approvedDocsCount = Document::where('status', 'Approved')->count();
        $rejectedDocsCount = Document::where('status', 'Rejected')->count();
        $draftDocsCount = Document::where('status', 'Draft')->count();

        $officesCount = Office::count();
        $usersCount = User::count();

        $statusTotal = max(1, $totalDocsCount);
        $approvedPct = round(($approvedDocsCount / $statusTotal) * 100, 1);
        $sentPct = round(($sentDocsCount / $statusTotal) * 100, 1);
        $draftPct = round(($draftDocsCount / $statusTotal) * 100, 1);
        $rejectedPct = round(($rejectedDocsCount / $statusTotal) * 100, 1);

        $docTypes = DocumentType::withCount('documents')->get();
        $docTypeTotal = max(1, $docTypes->sum('documents_count'));

        $topOffices = Office::withCount(['sentDocuments', 'receivedDocuments'])
            ->get()
            ->map(function (Office $office) {
                $office->total_activity = $office->sent_documents_count + $office->received_documents_count;
                return $office;
            })
            ->sortByDesc('total_activity')
            ->take(4)
            ->values();

        $historyLogs = DocumentLog::with(['document', 'user'])
            ->whereIn('action', ['viewed', 'sent'])
            ->latest()
            ->take(7)
            ->get();

        $currentYear = (int) date('Y');
        $startYear = 2025;
        $yearlyDocsMap = Document::whereNotNull('created_at')
            ->get(['created_at'])
            ->groupBy(fn (Document $document) => $document->created_at->format('Y'))
            ->map(fn ($group) => $group->count());
        $yearlyOfficesMap = Office::whereNotNull('created_at')
            ->get(['created_at'])
            ->groupBy(fn (Office $office) => $office->created_at->format('Y'))
            ->map(fn ($group) => $group->count());
        $yearlyUsersMap = User::whereNotNull('created_at')
            ->get(['created_at'])
            ->groupBy(fn (User $user) => $user->created_at->format('Y'))
            ->map(fn ($group) => $group->count());

        $yearlyChartData = [
            'Documents' => [],
            'Offices' => [],
            'Users' => [],
        ];

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $yearString = (string) $year;
            $yearlyChartData['Documents'][$yearString] = $yearlyDocsMap->get($yearString, 0);
            $yearlyChartData['Offices'][$yearString] = $yearlyOfficesMap->get($yearString, 0);
            $yearlyChartData['Users'][$yearString] = $yearlyUsersMap->get($yearString, 0);
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
            'startYear'
        ));
    }
}
