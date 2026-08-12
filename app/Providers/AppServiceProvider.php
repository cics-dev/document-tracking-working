<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ExternalDocument; 
use App\Models\Document;
use App\Models\Office;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        View::composer([
            'components.layouts.app', 
            'components.layouts.app.sidebar' 
        ], function ($view) {
            $user = Auth::user();
            $userOffice = $user->office;
            $userId = $user->id;
            
            $unreadExternal = 0;
            $unreadReceived = 0;
            $unreadAll = 0;

            if ($user) {
                $extQuery = ExternalDocument::query();
                
                $isPrivileged = $user->position == 'Staff' 
                             || $user->position == 'University President' 
                             || optional($user->office)->name == 'Records Section';

                if (!$isPrivileged) {
                    $extQuery->where('to_id', $user->office_id);
                }

                $unreadExternal = $extQuery->whereDoesntHave('accessLogs', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('action', 'Viewed');
                })->count();

                $filterPendingDocuments = function ($documents, $userId) {
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
                };

                $logConstraint = fn($q) => $q->where('user_id', $userId)->where('action', 'Viewed');

                $docs = collect();

                $directDocs = $userOffice ? $userOffice->receivedDocuments()
                    ->with(['documentType', 'toOffice', 'accessLogs' => $logConstraint])
                    ->get() : collect();

                $stepDocs = Document::whereHas('steps', fn($q) => $q->where('user_id', $userId))
                    ->where('status', '!=', 'Draft')
                    ->with(['documentType', 'fromOffice', 'steps', 'accessLogs' => $logConstraint])
                    ->get();
                $stepDocs = $filterPendingDocuments($stepDocs, $userId);

                $cfDocs = Document::whereHas('cfs', fn($q) => $q->where('user_id', $userId))
                    ->where('status', '!=', 'Draft')
                    ->with(['documentType', 'fromOffice', 'cfs', 'accessLogs' => $logConstraint])
                    ->get();

                $docs = $docs
                    ->merge($directDocs)
                    ->merge($stepDocs)
                    ->merge($cfDocs)
                    ->unique('id')
                    ->values();

                // if ($userOffice && $userOffice->name === 'Administration') {
                //     $presidentOfficeId = Office::whereRelation('users', 'position', 'University President')->value('id');
                //     $presidentUserId = Office::whereRelation('users', 'position', 'University President')->value('head_id');
                    
                //     if ($presidentOfficeId) {
                //         $presidentDocs = Document::where(function ($q) {
                //                 $q->where('document_type_id', 3)
                //                 ->orWhere(function ($subQuery) {
                //                     $subQuery->where('document_type_id', 1)
                //                             ->where('status', '!=', 'Draft'); 
                //                 });
                //             })
                //             ->when($user->role_id == 4, function ($query) {
                //                 $query->whereDoesntHave('steps', fn($q) => $q->where('step_type', 'routing')->whereHas('user', fn($sub) => $sub->where('office_id', 19)));
                //             }, function ($query) {
                //                 $query->whereHas('steps', fn($q) => $q->where('step_type', 'routing')->whereHas('user', fn($sub) => $sub->where('office_id', 19)));
                //             })
                //             ->with(['documentType', 'fromOffice', 'steps.user.office', 'accessLogs' => $logConstraint])
                //             ->get();

                //         $presidentDocs = $filterPendingDocuments($presidentDocs, $presidentUserId);
                //         $docs = $docs->merge($presidentDocs)->unique('id')->values();
                //     }
                // }

                if ($user->position == 'University President') {
                    $docs = $docs->reject(fn($doc) => in_array($doc->document_type_id, [1, 3]));
                }

                $docs = $docs->filter(function ($doc) use ($userId) {
                    $isStep = $doc->steps->contains('user_id', $userId);
                    $isRouting = $doc->steps->where('step_type', 'routing')->contains('user_id', $userId);
                    $isCf = $doc->cfs->contains('user_id', $userId) || $isRouting;
                    $isRecipient = optional($doc->toOffice)->head_id == $userId;

                    if ($isStep && ($isCf || $isRecipient)) {
                        return true;
                    }

                    if ($isCf || $isRecipient) {
                        return in_array($doc->status, ['Approved', 'Distributed']);
                    }

                    return true;
                });

                $unreadReceived = $docs->where(fn($doc) => $doc->accessLogs->isEmpty())->count();

                $unreadAll = Document::query()
                    ->whereDoesntHave('accessLogs', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('action', 'Viewed');
                    })->count();
            }

            $view->with('unreadExternalCount', $unreadExternal);
            $view->with('unreadReceivedCount', $unreadReceived);
            $view->with('unreadAllCount', $unreadAll);
        });
    }
}