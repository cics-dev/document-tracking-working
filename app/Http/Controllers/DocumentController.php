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
        $user = Auth::user();
        $userId = $user->id;
        $userOffice = $user->office;

        if ($mode === 'all') {
            return Document::with(['documentType', 'fromOffice', 'cfs', 'steps'])->get();
        }

        else if ($mode === 'Sent') {
            if ($userOffice->name === 'Administration') {
                $ownDocs = Document::where('from_id', $userOffice->id)
                    ->with(['documentType', 'fromOffice', 'steps'])
                    ->get();

                $presidentOfficeId = Office::whereHas('users', function ($query) {
                    $query->where('position', 'University President');
                })->value('id');

                if ($presidentOfficeId) {
                    $presidentDocs = Document::where('from_id', $presidentOfficeId)
                        ->with(['documentType', 'fromOffice', 'steps'])
                        ->get();
                    $ownDocs = $ownDocs->merge($presidentDocs);
                }

                return $ownDocs;
            }

            return $userOffice->sentDocuments()
                ->with('documentType', 'toOffice')
                ->get();
        }

        else if ($mode === 'received') {
            function filterPendingDocuments($documents, $userId) {
                return $documents->filter(function ($document) use ($userId) {
                    $sequence = $document->steps->sortBy('sequence')->values();

                    if ($sequence->isEmpty()) return true;
                    $mySequence = $sequence->firstWhere('user_id', $userId);
                    if (!$mySequence) return false;

                    $beforeMine = $sequence->takeWhile(fn($seq) => $seq !== $mySequence);
                    return $beforeMine->every(function ($seq) {
                        return !empty($seq->processed_at);
                    });
                })->values();
            }

            $docs = collect();

            $directDocs = $userOffice->receivedDocuments()
                ->with(['documentType', 'toOffice'])
                ->get()
                ->reject(function ($doc) use ($userId) {
                    return $doc->steps->contains('user_id', $userId);
                });

            $stepDocs = Document::whereHas('steps', fn($q) => $q->where('user_id', $userId))
                ->where('status', '!=', 'Draft')
                ->with(['documentType', 'fromOffice', 'steps'])
                ->get();

            $stepDocs = filterPendingDocuments($stepDocs, $userId);

            $cfDocs = Document::whereHas('cfs', fn($q) => $q->where('user_id', $userId))
                ->where('status', '!=', 'Draft')
                ->with(['documentType', 'fromOffice', 'cfs'])
                ->get();

            $docs = $docs
                ->merge($directDocs)
                ->merge($stepDocs)
                ->merge($cfDocs)
                ->unique('id')
                ->values();

            $docs->transform(function ($doc) use ($userId) {
                $doc->isStep = $doc->steps->contains('user_id', $userId);
                $doc->isCf = $doc->cfs->contains('user_id', $userId) || $doc->isStep;
                $doc->isRecipient = optional($doc->toOffice)->head_id == $userId;
                return $doc;
            });

            $docs = $docs->filter(function ($doc) {
                if ($doc->isStep && ($doc->isCf || $doc->isRecipient)) {
                    return true;
                }

                if ($doc->isCf || $doc->isRecipient) {
                    return in_array($doc->status, ['Approved', 'Distributed']);
                }

                return true;
            })->values();

            return $docs;
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
        return Document::with(['fromOffice', 'toOffice', 'documentType', 'steps'])->findOrFail($id);
    }

    public function getDocument($number) {
        return Document::with([
            'fromOffice.head',
            'toOffice.head',
            'documentType',
            'attachments',
            'externalDocuments',
            'steps.user.office.head',
            'cfs.user.office.head',
        ])->where('document_number', $number)->firstOrFail();
    }
}