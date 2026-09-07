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
<<<<<<< HEAD
        $user = Auth::user();
        $userId = $user->id;
        $userOffice = $user->office;
        $officeId = $userOffice->id;
=======
        $officeId = Auth::user()->office->id;
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045

        if ($mode === 'all') {
            return Document::with(['documentType', 'fromOffice', 'cfs', 'signatories', 'routings'])->get();
        }

        else if ($mode === 'sent') {
<<<<<<< HEAD
            if ($userOffice->name === 'Administration') {
                $ownDocs = Document::where('from_id', $userOffice->id)
                    ->with(['documentType', 'fromOffice', 'signatories'])
                    ->get();

                $presidentOfficeId = Office::whereHas('users', function ($query) {
                    $query->where('position', 'University President');
                })->value('id');

                if ($presidentOfficeId) {
                    $presidentDocs = Document::where('from_id', $presidentOfficeId)
                        ->with(['documentType', 'fromOffice', 'signatories'])
                        ->get();
                    $ownDocs = $ownDocs->merge($presidentDocs);
                }

                return $ownDocs;
            }

            return $userOffice->sentDocuments()
=======
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
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
                ->with('documentType', 'toOffice')
                ->get();
        }

        else if ($mode === 'received') {
<<<<<<< HEAD
            // Helper: check sequence completion
            function filterPendingDocuments($documents, $userId) {
                return $documents->filter(function ($document) use ($userId) {
                    $sequence = collect()
                        ->merge($document->routings->sortBy('created_at')->values())
                        ->merge($document->signatories->sortBy('sequence')->values());

                    if ($sequence->isEmpty()) return true;
                    $mySequence = $sequence->firstWhere('user_id', $userId);
                    if (!$mySequence) return false;

                    $beforeMine = $sequence->takeWhile(fn($seq) => $seq !== $mySequence);
                    return $beforeMine->every(function ($seq) {
                        return !empty($seq->reviewed_at) || !empty($seq->signed_at);
                    });
                })->values();
            }

            // --- MAIN COLLECTION ---
            $docs = collect();

            // 1️⃣ Direct recipient
=======
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
                    


                    // $routings = $document->routings->sortBy('created_at')->values();

                    // if ($routings->isNotEmpty()) {
                    //     $pendingRouting = $routings->firstWhere('status', 'pending');
                    //     dd($pendingRouting);
                        
                    //     if ($pendingRouting && $pendingRouting->user_id == $userId) {
                    //         $isFirstPending = $routings
                    //             ->takeWhile(fn($r) => $r->id != $pendingRouting->id)
                    //             ->every(fn($r) => $r->status == 'reviewed');
                            
                    //         if ($isFirstPending) {
                    //             return true;
                    //         }
                    //     }
                        
                    //     if ($pendingRouting) {
                    //         return false;
                    //     }
                        
                    //     if ($routings->where('status', 'returned')->isNotEmpty()) {
                    //         return false;
                    //     }
                    // }

                    // $signatories = $document->signatories->sortBy('sequence')->values();
                    // if ($signatories->isEmpty()) {
                    //     return true;
                    // }
                    // $current = $signatories->firstWhere('user_id', $userId);
        
                    // if (!$current) return false;
        
                    // return $signatories
                    //     ->filter(fn($sig) => $sig->sequence < $current->sequence)
                    //     ->every(fn($sig) => !is_null($sig->signed_at));
                })->values();
            }

>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
            $directDocs = $userOffice->receivedDocuments()
                ->with(['documentType', 'toOffice'])
                ->get();

<<<<<<< HEAD
            // 2️⃣ Routing docs
            $routingDocs = Document::whereHas('routings', fn($q) => $q->where('user_id', $userId))
                ->where('status', '!=', 'draft')
                ->with(['documentType', 'fromOffice', 'routings'])
                ->get();

            $routingDocs = filterPendingDocuments($routingDocs, $userId);

            // 3️⃣ Signatory docs
            $signatoryDocs = Document::whereHas('signatories', fn($q) => $q->where('user_id', $userId))
                ->where('status', '!=', 'draft')
                ->with(['documentType', 'fromOffice', 'signatories'])
                ->get();

            $signatoryDocs = filterPendingDocuments($signatoryDocs, $userId);

            // 4️⃣ CF docs
            $cfDocs = Document::whereHas('cfs', fn($q) => $q->where('user_id', $userId))
                ->where('status', '!=', 'draft')
                ->with(['documentType', 'fromOffice', 'cfs'])
                ->get();

            // Merge all
            $docs = $docs
                ->merge($directDocs)
                ->merge($routingDocs)
                ->merge($signatoryDocs)
                ->merge($cfDocs)
                ->unique('id')
                ->values();

            // 5️⃣ President logic
            if ($userOffice->name === 'Administration') {
                $presidentOfficeId = Office::whereRelation('users', 'position', 'University President')->value('id');
                $presidentUserId = Office::whereRelation('users', 'position', 'University President')->value('head_id');
                
                if ($presidentOfficeId) {
                    $presidentDocs = Document::where(function ($q) {
                            $q->where('document_type_id', 3)
                            ->orWhere(function ($subQuery) {
                                $subQuery->where('document_type_id', 1)
                                        ->where('status', '!=', 'draft'); // Only applies if type is 1
                            });
                        })
                        ->when($user->role_id == 4, function ($query) {
                            $query->whereDoesntHave('routings.user', fn($q) => $q->where('office_id', 19));
                        }, function ($query) {
                            $query->whereHas('routings.user', fn($q) => $q->where('office_id', 19));
                        })
                        ->with(['documentType', 'fromOffice', 'signatories', 'routings.user'])
                        ->get();

                    $presidentDocs = filterPendingDocuments($presidentDocs, $presidentUserId);
                    $docs = $docs->merge($presidentDocs)->unique('id')->values();
                }
            }

            // 6️⃣ Filter out certain doc types for President
            if ($user->position == 'University President') {
                $docs = $docs->reject(fn($doc) => in_array($doc->document_type_id, [1, 3]));
            }

            // 7️⃣ Tag each doc with relationship flags (like your first code)
            $docs->transform(function ($doc) use ($userId) {
                $doc->isSignatory = $doc->signatories->contains('user_id', $userId);
                $doc->isRouting = $doc->routings->contains('user_id', $userId);
                $doc->isCf = $doc->cfs->contains('user_id', $userId) || $doc->isRouting;
                $doc->isRecipient = optional($doc->toOffice)->head_id == $userId;
                return $doc;
            });

            $docs = $docs->filter(function ($doc) {
                // If user is both (signatory/routing) and (cf/recipient) → allow all
                if (($doc->isSignatory || $doc->isRouting) && ($doc->isCf || $doc->isRecipient)) {
                    return true;
                }

                // If user is only CF or Recipient → allow only Approved/Distributed
                if ($doc->isCf || $doc->isRecipient) {
                    return in_array($doc->status, ['Approved', 'Distributed']);
                }

                // Otherwise, allow (signatory/routing/other)
                return true;
            })->values();

            return $docs;
        }
    }


=======
            $directDocs = filterPendingDocuments($directDocs, $userId);

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

>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
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
<<<<<<< HEAD
        return Document::with(['fromOffice', 'toOffice', 'documentType', 'signatories'])->where('document_number', $number)->first();
=======
        return Document::with(['fromOffice', 'toOffice', 'documentType', 'signatories'])->where('document_number', $number)->first();;
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    }
}
