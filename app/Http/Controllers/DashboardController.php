<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentLog;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\User;
use App\Services\DocumentQueryService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $startOfMonth = now()->startOfMonth();
        $startOfPreviousMonth = $startOfMonth->copy()->subMonth();

        $periods = [
            'total' => ['1 = 1', []],
            'currentMonth' => ['created_at >= ?', [$startOfMonth]],
            // Keep the existing inclusive month boundaries used by whereBetween.
            'previousMonth' => ['created_at BETWEEN ? AND ?', [$startOfPreviousMonth, $startOfMonth]],
        ];
        $months = collect(range(5, 0))->map(function (int $monthsAgo) use ($startOfMonth, &$periods) {
            $month = $startOfMonth->copy()->subMonths($monthsAgo);
            $periods['month_'.$monthsAgo] = ['created_at BETWEEN ? AND ?', [$month, $month->copy()->addMonth()]];

            return ['label' => $month->format('M Y'), 'period' => 'month_'.$monthsAgo];
        });

        $internal = $this->countsByPeriod(Document::query(), $periods, [
            'all' => '1 = 1',
            'files' => 'file_url IS NOT NULL',
            'received' => "to_id IS NOT NULL AND status != 'Draft'",
        ]);
        $external = $this->countsByPeriod(ExternalDocument::query(), $periods)['all'];
        $metricPeriods = array_intersect_key($periods, array_flip(['total', 'currentMonth', 'previousMonth']));
        $attachments = $this->countsByPeriod(DocumentAttachment::query(), $metricPeriods)['all'];
        $users = $this->countsByPeriod(User::query(), $metricPeriods)['all'];
        $offices = $this->countsByPeriod(Office::query(), $metricPeriods)['all'];

        $metrics = [
            $this->metricFromCounts('Documents', [$internal['all'], $external], 'document-text', 'blue'),
            $this->metricFromCounts('Users', [$users], 'users', 'green'),
            $this->metricFromCounts('Files', [$attachments, $internal['files'], $external], 'paper-clip', 'orange'),
            $this->metricFromCounts('Offices', [$offices], 'building-office-2', 'amber'),
            $this->metricFromCounts('Received', [$internal['received'], $external], 'inbox-arrow-down', 'violet'),
        ];

        $months = $months->map(fn (array $month) => [
            'label' => $month['label'],
            'internal' => $internal['all'][$month['period']],
            'external' => $external[$month['period']],
        ]);

        $statusCounts = Document::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->pluck('total', 'status');

        $recentActivity = DocumentLog::with(['document:id,document_number,subject', 'user:id,name'])
            ->whereHas('document', function (Builder $query): void {
                $user = auth()->user();
                if ($user->hasAccess('view_all_documents')) {
                    return;
                }

                $documents = app(DocumentQueryService::class);
                $query->where(function (Builder $visible) use ($user, $documents): void {
                    $visible->whereRaw('1 = 0');
                    if ($user->hasAccess('send_documents')) {
                        $visible->orWhereIn('documents.id', $documents->listFor($user, 'Sent')->select('documents.id'));
                    }
                    if ($user->hasAccess('receive_documents')) {
                        $visible->orWhereIn('documents.id', $documents->receivedBy(Document::query(), $user)->select('documents.id'));
                    }
                });
            })
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact('metrics', 'months', 'statusCounts', 'recentActivity'));
    }

    private function countsByPeriod(Builder $query, array $periods, array $groups = ['all' => '1 = 1']): array
    {
        foreach ($groups as $group => $condition) {
            foreach ($periods as $period => [$dateCondition, $bindings]) {
                $alias = $query->getQuery()->getGrammar()->wrap($group.'_'.$period);
                $query->selectRaw(
                    "COUNT(CASE WHEN ($condition) AND ($dateCondition) THEN 1 END) AS $alias",
                    $bindings,
                );
            }
        }

        $row = $query->first();
        $counts = [];

        foreach ($groups as $group => $condition) {
            foreach ($periods as $period => $definition) {
                $counts[$group][$period] = (int) $row->getAttribute($group.'_'.$period);
            }
        }

        return $counts;
    }

    private function metricFromCounts(string $label, array $sources, string $icon, string $color): array
    {
        return $this->metric(
            $label,
            array_sum(array_column($sources, 'total')),
            array_sum(array_column($sources, 'currentMonth')),
            array_sum(array_column($sources, 'previousMonth')),
            $icon,
            $color,
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
