<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index($mode)
    {
        $officeId = Auth::user()->office->id;

        if ($mode === 'sent') {
            $userId = Auth::id();
            $userOffice = Auth::user()->office;
            if ($userOffice->name === 'Administration') {
                $presidentOfficeId = Office::whereHas('users', function ($query) {
                    $query->where('position', 'University President');
                })->pluck('id')->first();
            
                if ($presidentOfficeId) {
                    // Fetch documents addressed to the University President's office
                    $presidentDocs = Document::where('from_id', $presidentOfficeId)
                        ->with(['documentType', 'fromOffice', 'signatories'])
                        ->get();
            
                    return $presidentDocs;
                }
            }

            return Auth::user()->office->sentDocuments()
                ->with('documentType', 'toOffice')
                ->get();
        }

        if ($mode === 'received') {
            $userId = Auth::id();
            $userOffice = Auth::user()->office;

            function filterPendingDocuments($documents, $userId) {
                return $documents->filter(function ($document) use ($userId) {
                    $signatories = $document->signatories->sortBy('sequence')->values();
                    if ($signatories->isEmpty()) {
                        return true;
                    }
                    $current = $signatories->firstWhere('user_id', $userId);
        
                    if (!$current) return false;
        
                    return $signatories
                        ->filter(fn($sig) => $sig->sequence < $current->sequence)
                        ->every(fn($sig) => !is_null($sig->signed_at));
                })->values();
            }

            $directDocs = $userOffice->receivedDocuments()
                ->with(['documentType', 'toOffice'])
                ->get();

            $directDocs = filterPendingDocuments($directDocs, $userId);

            $signatoryDocs = Document::whereHas('signatories', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('status', '!=', 'draft')
            ->with(['documentType', 'fromOffice', 'signatories'])
            ->get();

            $signatoryDocs = filterPendingDocuments($signatoryDocs, $userId);
            
            $directDocs = $directDocs->merge($signatoryDocs)->unique('id')->values();

            $cfDocs = Document::whereHas('cfs', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('status', '!=', 'draft')
            ->with(['documentType', 'fromOffice', 'cfs'])
            ->get();

            $cfDocs = filterPendingDocuments($cfDocs, $userId);
            $directDocs = $directDocs->merge($cfDocs)->unique('id')->values();

            if ($userOffice->name === 'Administration') {
                $presidentOfficeId = Office::whereRelation('users', 'position', 'University President')->value('id');
                $presidentUserId = Office::whereRelation('users', 'position', 'University President')->value('head_id');
            
                if ($presidentOfficeId) {
                    $presidentDocs = Document::where('to_id', $presidentOfficeId)
                        ->with(['documentType', 'fromOffice', 'signatories'])
                        ->get();
                }

                $presidentDocs = filterPendingDocuments($presidentDocs, $presidentUserId);
                $directDocs = $directDocs->merge($presidentDocs)->unique('id')->values();
            }
    
    
            return $directDocs;
            // return $signatoryDocs;
        }
    }

    public function store(Request $request)
    {
        $request->merge([
            'name' => trim(
                $request->given_name . ' ' .
                ($request->middle_initial != '' ? $request->middle_initial . '. ' : '') .
                $request->family_name .
                ($request->suffix != '' ? ' ' . $request->suffix : '')
            ),
            'password' => 'secret'
        ]);
        $user = Document::create($request->all());
        $user->profile()->create($request->all());
        if ($request->is_head) {
            $user->office()->update([
                'head_id' => $user->id,
            ]);
        }
        return [$user, $user->profile];
    }

    public function show(int $id) {
        return Document::with(['fromOffice', 'toOffice', 'documentType', 'signatories'])->findOrFail($id);
    }

    public function getDocument($number) {
        return Document::with(['fromOffice', 'toOffice', 'documentType', 'signatories'])->where('document_number', $number)->first();;
    }
}
