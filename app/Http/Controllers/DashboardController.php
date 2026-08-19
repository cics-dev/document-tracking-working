<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentLog;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $startOfMonth = now()->startOfMonth();
        $startOfPreviousMonth = $startOfMonth->copy()->subMonth();

        $internalDocuments = Document::query();
        $externalDocuments = ExternalDocument::query();

        $metrics = [
            $this->metric(
                'Documents',
                (clone $internalDocuments)->count() + (clone $externalDocuments)->count(),
                (clone $internalDocuments)->where('created_at', '>=', $startOfMonth)->count()
                    + (clone $externalDocuments)->where('created_at', '>=', $startOfMonth)->count(),
                (clone $internalDocuments)->whereBetween('created_at', [$startOfPreviousMonth, $startOfMonth])->count()
                    + (clone $externalDocuments)->whereBetween('created_at', [$startOfPreviousMonth, $startOfMonth])->count(),
                'document-text',
                'blue'
            ),
            $this->metricFromQuery('Users', User::query(), 'users', 'green'),
            $this->metric(
                'Files',
                DocumentAttachment::count() + Document::whereNotNull('file_url')->count() + ExternalDocument::count(),
                DocumentAttachment::where('created_at', '>=', $startOfMonth)->count()
                    + Document::whereNotNull('file_url')->where('created_at', '>=', $startOfMonth)->count()
                    + ExternalDocument::where('created_at', '>=', $startOfMonth)->count(),
                DocumentAttachment::whereBetween('created_at', [$startOfPreviousMonth, $startOfMonth])->count()
                    + Document::whereNotNull('file_url')->whereBetween('created_at', [$startOfPreviousMonth, $startOfMonth])->count()
                    + ExternalDocument::whereBetween('created_at', [$startOfPreviousMonth, $startOfMonth])->count(),
                'paper-clip',
                'orange'
            ),
            $this->metricFromQuery('Offices', Office::query(), 'building-office-2', 'amber'),
            $this->metric(
                'Received',
                Document::whereNotNull('to_id')->where('status', '!=', 'Draft')->count() + ExternalDocument::count(),
                Document::whereNotNull('to_id')->where('status', '!=', 'Draft')->where('created_at', '>=', $startOfMonth)->count()
                    + ExternalDocument::where('created_at', '>=', $startOfMonth)->count(),
                Document::whereNotNull('to_id')->where('status', '!=', 'Draft')->whereBetween('created_at', [$startOfPreviousMonth, $startOfMonth])->count()
                    + ExternalDocument::whereBetween('created_at', [$startOfPreviousMonth, $startOfMonth])->count(),
                'inbox-arrow-down',
                'violet'
            ),
        ];

        $months = collect(range(5, 0))->map(function (int $monthsAgo) {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $end = $month->copy()->addMonth();

            return [
                'label' => $month->format('M Y'),
                'internal' => Document::whereBetween('created_at', [$month, $end])->count(),
                'external' => ExternalDocument::whereBetween('created_at', [$month, $end])->count(),
            ];
        });

        $statusCounts = Document::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->pluck('total', 'status');

        $recentActivity = DocumentLog::with(['document:id,document_number,subject', 'user:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact('metrics', 'months', 'statusCounts', 'recentActivity'));
    }

    private function metricFromQuery(string $label, Builder $query, string $icon, string $color): array
    {
        $startOfMonth = now()->startOfMonth();
        $startOfPreviousMonth = $startOfMonth->copy()->subMonth();

        return $this->metric(
            $label,
            (clone $query)->count(),
            (clone $query)->where('created_at', '>=', $startOfMonth)->count(),
            (clone $query)->whereBetween('created_at', [$startOfPreviousMonth, $startOfMonth])->count(),
            $icon,
            $color
        );
    }

    private function metric(string $label, int $total, int $currentMonth, int $previousMonth, string $icon, string $color): array
    {
        $change = $previousMonth === 0
            ? ($currentMonth > 0 ? 100.0 : 0.0)
            : (($currentMonth - $previousMonth) / $previousMonth) * 100;

        return compact('label', 'total', 'currentMonth', 'previousMonth', 'change', 'icon', 'color');
    }
}
