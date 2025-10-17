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

        if ($mode === 'all') {
            return Document::with(['documentType', 'fromOffice', 'cfs', 'signatories', 'routings'])->get();
        }

        else if ($mode === 'sent') {
            $userId = Auth::id();
            $userOffice = Auth::user()->office;
            if ($userOffice->name === 'Administration') {
                $ownDocs = Document::where('from_id', $userOffice->id)
                    ->with(['documentType', 'fromOffice', 'signatories'])
                    ->get();

                $allDocs = $ownDocs;
                $presidentOfficeId = Office::whereHas('users', function ($query) {
                    $query->where('position', 'University President');
                })->pluck('id')->first();
            
                if ($presidentOfficeId) {
                    // Fetch documents addressed to the University President's office
                    $presidentDocs = Document::where('from_id', $presidentOfficeId)
                        ->with(['documentType', 'fromOffice', 'signatories'])
                        ->get();
            
                    $allDocs = $ownDocs->merge($presidentDocs);
                }
                return $allDocs;
            }

            return Auth::user()->office->sentDocuments()
                ->with('documentType', 'toOffice')
                ->get();
        }

        else if ($mode === 'received') {
            $userId = Auth::id();
            $userOffice = Auth::user()->office;

            function filterPendingDocuments($documents, $userId) {
                return $documents->filter(function ($document) use ($userId) {
                    $sequence = [];
                    $routings = $document->routings->sortBy('created_at')->values();
                    $signatories = $document->signatories->sortBy('sequence')->values();

                    foreach ($routings as $routing) array_push($sequence, $routing);
                    foreach ($signatories as $signatory) array_push($sequence, $signatory);
                    $sequenceCollection = collect($sequence);
                    
                    if ($sequenceCollection->isEmpty()) {
                        return true;
                    }
                    $mySequence = $sequenceCollection->firstWhere('user_id', $userId);
                    
                    if (!$mySequence) return false;

                    return $sequenceCollection
                        ->filter(function($sequence, $index) use ($mySequence, $sequenceCollection) {
                            $myIndex = $sequenceCollection->search(function ($item) use ($mySequence) {
                                return $item === $mySequence;
                            });
                            
                            return $index < $myIndex;
                        })
                        ->every(function($sequence) {
                            if (isset($sequence->reviewed_at)) {
                                return !is_null($sequence->reviewed_at);
                            }
                            elseif (isset($sequence->signed_at)) {
                                return !is_null($sequence->signed_at);
                            }
                            return false;
                        });
                })->values();
            }

            $directDocs = $userOffice->receivedDocuments()
                ->with(['documentType', 'toOffice'])
                ->get();

            // $directDocs = filterPendingDocuments($directDocs, $userId);

            $routingDocs = Document::whereHas('routings', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('status', '!=', 'draft')
            ->with(['documentType', 'fromOffice', 'routings'])
            ->get();

            $routingDocs = filterPendingDocuments($routingDocs, $userId);
            
            $directDocs = $directDocs->merge($routingDocs)->unique('id')->values();

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

            // $cfDocs = filterPendingDocuments($cfDocs, $userId);
            $directDocs = $directDocs->merge($cfDocs)->unique('id')->values();
            
            if ($userOffice->name === 'Administration') {
                $presidentOfficeId = Office::whereRelation('users', 'position', 'University President')->value('id');
                $presidentUserId = Office::whereRelation('users', 'position', 'University President')->value('head_id');
                
                if ($presidentOfficeId) {
                    if(Auth::user()->role_id == 4) {
                        $presidentDocs = Document::where('to_id', $presidentOfficeId)
                        ->whereDoesntHave('routings.user', function ($query) {
                            $query->where('office_id', 19); // ❌ Exclude documents with routings from office_id 19
                        })
                        ->with(['documentType', 'fromOffice', 'signatories', 'routings.user'])
                        ->get();
                    }
                    else {
                        $presidentDocs = Document::where('to_id', $presidentOfficeId)
                        ->whereHas('routings.user', function ($query) {
                            $query->where('office_id', 19); // ✅ Only include routings from office_id 19
                        })
                        ->with(['documentType', 'fromOffice', 'signatories', 'routings.user'])
                        ->get();
                    }

                    $presidentDocs = filterPendingDocuments($presidentDocs, $presidentUserId);
                    $directDocs = $directDocs->merge($presidentDocs)->unique('id')->values();
                }
            }

            if(auth()->user()->position == 'University President') {
                $directDocs = $directDocs->reject(function ($doc) {
                    return $doc->document_type_id == 1;
                });
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
        return Document::with(['fromOffice', 'toOffice', 'documentType', 'signatories'])->where('document_number', $number)->first();
    }
}
