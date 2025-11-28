<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\DocumentController;

class ListDocuments extends Component
{
    use WithPagination;

    public $office_name;
    public $documentTypeTab = 'inter';
    public string $mode = 'received';
    
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';

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

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedTypeFilter() { $this->resetPage(); }

    public function render()
    {
        $rawDocuments = app(DocumentController::class)->index($this->mode);

        $documents = collect($rawDocuments);

        if ($this->documentTypeTab === 'intra') {
            $documents = $documents->where('document_level', 'Intra');
        } else {
            $documents = $documents->where('document_level', '!=', 'Intra');
        }

        if (!empty($this->search)) {
            $documents = $documents->filter(function ($doc) {
                return stripos($doc->subject, $this->search) !== false 
                    || stripos($doc->document_number, $this->search) !== false;
            });
        }

        if (!empty($this->statusFilter)) {
            $documents = $documents->where('status', $this->statusFilter);
        }

        if (!empty($this->typeFilter)) {
            $documents = $documents->where('document_type_id', $this->typeFilter);
        }

        $documents = $documents->sortByDesc('updated_at');

        $perPage = 10;
        $currentPage = Paginator::resolveCurrentPage();
        
        $currentPageItems = $documents->slice(($currentPage - 1) * $perPage, $perPage)->values();

        if ($currentPageItems->isNotEmpty()) {
            \Illuminate\Database\Eloquent\Collection::make($currentPageItems)
                ->load(['accessLogs' => function ($q) {
                    $q->where('user_id', Auth::id())
                    ->where('action', 'viewed');
                }]);
        }

        $paginatedDocuments = new LengthAwarePaginator(
            $currentPageItems,
            $documents->count(), 
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        $documentTypes = DocumentType::where('name', '!=', 'Intra-Office Memorandum')->get();

        return view('livewire.documents.list-documents', [
            'documents' => $paginatedDocuments,
            'documentTypes' => $documentTypes,
        ]);
    }
    
    public function editDocument($id) { return redirect()->route('documents.create-document', ['id' => $id]); }
    public function viewDocument($number) { return redirect()->route('documents.view-document', ['number' => $number]); }
    public function trackDocument($number) { return redirect()->route('documents.track-document', ['number' => $number]); }
}