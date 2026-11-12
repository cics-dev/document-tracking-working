<?php

namespace App\Livewire\Documents;

use App\Models\DocumentStep;
use App\Models\Document;
use App\Models\Office;
use App\Models\DocumentType;
use App\Services\DocumentQueryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListDocuments extends Component
{
    use WithPagination;

    public $office_name;

    public $documentTypeTab = 'inter';

    public string $mode = 'received';

    public $search = '';

    public $statusFilter = '';

    public $typeFilter = '';

    public string $formatFilter = '';

    public string $officeFilter = '';

    public $dateFrom = '';

    public $dateTo = '';

    public string $sortBy = 'date_sent';

    public string $sortDirection = 'desc';

    public function mount($mode = 'received')
    {
        $requiredPermission = match ($mode) {
            'received' => 'receive_documents',
            'Sent' => 'send_documents',
            'all' => 'view_all_documents',
            default => abort(404),
        };
        abort_unless(Auth::user()->hasAccess($requiredPermission), 403, 'You do not have permission to view these documents.');
        $this->office_name = Auth::user()->office->name;
        $this->mode = $mode;
    }

    public function switchDocumentTypeTab($tab)
    {
        $this->documentTypeTab = $tab;
        $this->typeFilter = '';
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedOfficeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFormatFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'statusFilter', 'typeFilter', 'formatFilter', 'officeFilter', 'dateFrom', 'dateTo');
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['document_number', 'subject', 'status', 'created_at', 'updated_at', 'date_sent'], true)) {
            return;
        }

        $this->sortDirection = $this->sortBy === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortBy = $column;
        $this->resetPage();
    }

    public function render()
    {
        $documents = app(DocumentQueryService::class)->listFor(Auth::user(), $this->mode, $this->mode !== 'all' && $this->officeFilter !== '' ? (int) $this->officeFilter : null)
            ->when($this->mode === 'all', fn ($query) => $query->where('status', '!=', 'Draft'))
            ->when($this->documentTypeTab === 'intra', fn ($query) => $query->where('document_level', 'Intra'))
            ->when($this->documentTypeTab !== 'intra', fn ($query) => $query->where(fn ($levels) => $levels
                ->where('document_level', '!=', 'Intra')
                ->when($this->mode !== 'Sent', fn ($levels) => $levels->orWhere('is_physical', true))))
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('subject', 'like', "%{$this->search}%")
                    ->orWhere('document_number', 'like', "%{$this->search}%");
            }))
            ->when($this->formatFilter === 'physical', fn ($query) => $query->where('is_physical', true))
            ->when($this->formatFilter === 'digital', fn ($query) => $query->where('is_physical', false))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->typeFilter !== '', fn ($query) => $query->where('document_type_id', $this->typeFilter))
            ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->withExists(['accessLogs as is_viewed_by_me' => fn ($query) => $query->where('user_id', Auth::id())->where('action', 'Viewed')]);

        if ($this->mode === 'received') {
            $actionIds = $this->actionableDocumentIds(clone $documents);
            if ($actionIds !== []) {
                $placeholders = implode(',', array_fill(0, count($actionIds), '?'));
                $documents->selectRaw("CASE WHEN documents.id IN ($placeholders) THEN 1 ELSE 0 END AS needs_my_action", $actionIds);
            } else {
                $documents->selectRaw('0 AS needs_my_action');
            }
            $documents->orderByDesc('needs_my_action');
        } elseif ($this->mode === 'Sent') {
            $documents->orderByRaw("CASE WHEN status = 'Draft' THEN 0 ELSE 1 END");
        }

        if ($this->sortBy === 'date_sent') {
            $documents->orderByRaw("CASE WHEN status = 'Draft' THEN created_at ELSE COALESCE(date_sent, created_at) END ".($this->sortDirection === 'asc' ? 'asc' : 'desc'));
        } else {
            $documents->orderBy($this->sortBy, $this->sortDirection);
        }
        $documents = $documents->orderByDesc('created_at')->orderByDesc('documents.id')->paginate(10);

        $documentTypes = DocumentType::where('document_level', '!=', 'Intra')->get();

        $showOfficeContext = $this->mode !== 'all' && Auth::user()->managesMultipleOffices();
        $officeOptions = Office::whereIn('id', Auth::user()->workflowOfficeIds())->orderBy('name')->get();
        $receivingOfficeLabels = [];
        if ($showOfficeContext && $this->mode === 'received') {
            foreach ($officeOptions as $office) {
                $receivedIds = app(DocumentQueryService::class)->receivedBy(
                    Document::whereIn('id', $documents->pluck('id')), Auth::user(), $office->id
                )->pluck('id');
                foreach ($receivedIds as $documentId) {
                    $receivingOfficeLabels[$documentId][] = $office->name;
                }
            }
        }

        return view('livewire.documents.list-documents', [
            'documents' => $documents,
            'documentTypes' => $documentTypes,
            'officeOptions' => $officeOptions,
            'showOfficeContext' => $showOfficeContext,
            'receivingOfficeLabels' => $receivingOfficeLabels,
        ])->layout('layouts.app');
    }

    private function actionableDocumentIds(Builder $documents): array
    {
        $user = Auth::user();

        // Only the next pending step can require action. Use the workflow's
        // assignment rules so heads, OICs and head-only document types agree.
        return DocumentStep::query()
            ->whereIn('document_id', $documents->select('documents.id'))
            ->where('status', 'Pending')
            ->whereHas('document', fn ($query) => $query
                ->whereIn('status', ['Sent', 'In Process'])
                ->orWhere(fn ($query) => $query->where('status', 'Approved')->where('document_steps.step_type', 'action')))
            ->whereNotExists(fn ($query) => $query->selectRaw('1')
                ->from('document_steps as previous_steps')
                ->whereColumn('previous_steps.document_id', 'document_steps.document_id')
                ->where('previous_steps.status', 'Pending')
                ->where(fn ($query) => $query
                    ->whereColumn('previous_steps.sequence', '<', 'document_steps.sequence')
                    ->orWhere(fn ($query) => $query
                        ->whereColumn('previous_steps.sequence', '=', 'document_steps.sequence')
                        ->whereColumn('previous_steps.id', '<', 'document_steps.id'))))
            ->with(['document.documentType', 'office.head', 'office.actingHead', 'user'])
            ->get()
            ->filter(fn (DocumentStep $step) => $step->isAssignedTo($user))
            ->pluck('document_id')->unique()->values()->all();
    }

    public function viewDocument($number)
    {
        return redirect()->route('documents.view-document', ['number' => $number, 'list' => $this->mode]);
    }

    public function trackDocument($number)
    {
        return redirect()->route('documents.track-document', ['number' => $number, 'list' => $this->mode]);
    }
}
