<?php

namespace App\Livewire\Documents;

use App\Models\DocumentType;
use App\Services\DocumentQueryService;
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

    public $dateFrom = '';

    public $dateTo = '';

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public function mount($mode = 'received')
    {
        if (Auth::user()->position === 'Administrator') {
            abort(403, 'Access denied.');
        }
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
        $this->reset('search', 'statusFilter', 'typeFilter', 'dateFrom', 'dateTo');
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['document_number', 'subject', 'status', 'created_at', 'updated_at'], true)) {
            return;
        }

        $this->sortDirection = $this->sortBy === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortBy = $column;
        $this->resetPage();
    }

    public function render()
    {
        $documents = app(DocumentQueryService::class)->listFor(Auth::user(), $this->mode)
            ->when($this->documentTypeTab === 'intra', fn ($query) => $query->where('document_level', 'Intra'))
            ->when($this->documentTypeTab !== 'intra', fn ($query) => $query->where('document_level', '!=', 'Intra'))
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('subject', 'like', "%{$this->search}%")
                    ->orWhere('document_number', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->typeFilter !== '', fn ($query) => $query->where('document_type_id', $this->typeFilter))
            ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->withExists(['accessLogs as is_viewed_by_me' => fn ($query) => $query->where('user_id', Auth::id())->where('action', 'Viewed')])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        $documentTypes = DocumentType::where('abbreviation', '!=', 'Intra')->get();

        return view('livewire.documents.list-documents', [
            'documents' => $documents,
            'documentTypes' => $documentTypes,
        ])->layout('layouts.app');
    }

    public function viewDocument($number)
    {
        return redirect()->route('documents.view-document', ['number' => $number]);
    }

    public function trackDocument($number)
    {
        return redirect()->route('documents.track-document', ['number' => $number]);
    }
}
