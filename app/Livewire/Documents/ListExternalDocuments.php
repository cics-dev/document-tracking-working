<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ExternalDocument;
use Illuminate\Support\Facades\Auth;

class ListExternalDocuments extends Component
{
    use WithPagination;

    public $search = '';

    // Reset pagination when searching to avoid empty pages
    public function updatedSearch() 
    { 
        $this->resetPage(); 
    }

    public function viewDocument($id)
    {
        return redirect()->route('documents.view-external-document', ['id' => $id]);
    }

    public function render()
    {
        $user = Auth::user();
        $query = ExternalDocument::query();

        $isPrivileged = $user->position == 'Staff' 
                     || $user->position == 'University President' 
                     || optional($user->office)->name == 'Records Section';

        if (!$isPrivileged) {
            $query->where('to_id', $user->office_id);
        }

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('subject', 'like', '%' . $this->search . '%')
                  ->orWhere('from', 'like', '%' . $this->search . '%');
            });
        }
        
        $query->withExists(['accessLogs as is_viewed_by_me' => function ($q) use ($user) {
            $q->where('user_id', $user->id)
            ->where('action', 'viewed');
        }]);

        $documents = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.documents.list-external-documents', [
            'documents' => $documents
        ]);
    }
}