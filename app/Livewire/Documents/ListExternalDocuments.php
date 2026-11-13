<?php

namespace App\Livewire\Documents;

use App\Models\Office;
use App\Services\ExternalDocumentQueryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListExternalDocuments extends Component
{
    use WithPagination;

    public $search = '';

    public $officeFilter = '';

    public $dateFrom = '';

    public $dateTo = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(): void
    {
        abort_unless(
            Auth::user()->hasAccess('view_all_external_documents') || Auth::user()->hasAccess('receive_external_documents') || Auth::user()->hasAccess('send_external_documents'),
            403,
            'You do not have permission to access external documents.'
        );
    }

    // Reset pagination when searching to avoid empty pages
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updated($property): void
    {
        if (in_array($property, ['officeFilter', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'officeFilter', 'dateFrom', 'dateTo');
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, ['document_number', 'from', 'subject', 'received_date', 'created_at'], true)) {
            return;
        }
        $this->sortDirection = $this->sortBy === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortBy = $column;
        $this->resetPage();
    }

    public function viewDocument($id)
    {
        return redirect()->route('documents.view-external-document', ['id' => $id]);
    }

    public function render()
    {
        $user = Auth::user();
        $query = app(ExternalDocumentQueryService::class)->listFor($user)->with('toOffice');
        $officeIds = $user->workflowOfficeIds();
        if (! $user->hasAccess('view_all_external_documents') && $this->officeFilter !== '') {
            abort_unless(in_array((int) $this->officeFilter, $officeIds, true), 403);
        }

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('subject', 'like', '%'.$this->search.'%')
                    ->orWhere('from', 'like', '%'.$this->search.'%');
            });
        }

        $query->when($this->officeFilter !== '', fn ($query) => $query->where('to_id', $this->officeFilter))
            ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('received_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($query) => $query->whereDate('received_date', '<=', $this->dateTo));

        $query->withExists(['accessLogs as is_viewed_by_me' => function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->where('action', 'Viewed');
        }]);

        $documents = $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);

        return view('livewire.documents.list-external-documents', [
            'documents' => $documents,
            'offices' => Office::query()
                ->when(! $user->hasAccess('view_all_external_documents'), fn ($query) => $query->whereIn('id', $officeIds))
                ->orderBy('name')->get(['id', 'name', 'abbreviation'])
                ->filter(fn ($office) => $user->hasAccess('view_all_external_documents')
                    || $user->hasAccess('receive_external_documents', $office->id)
                    || $user->hasAccess('send_external_documents', $office->id)),
            'showOfficeContext' => $user->managesMultipleOffices(),
            'allOfficesLabel' => $user->hasAccess('view_all_external_documents') ? 'All Offices' : 'All my offices',
        ])->layout('layouts.app');
    }
}
