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
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
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
            
            // Initialize default values
            $unreadExternal = 0;
            $unreadReceived = 0;
            $unreadAll = 0; // <--- NEW VARIABLE

            if ($user) {
                // ... (Your existing External logic) ...
                $extQuery = ExternalDocument::query();
                
                $isPrivileged = $user->position == 'Staff' 
                             || $user->position == 'University President' 
                             || optional($user->office)->name == 'Records Section';

                if (!$isPrivileged) {
                    $extQuery->where('to_id', $user->office_id);
                }

                $unreadExternal = $extQuery->whereDoesntHave('accessLogs', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('action', 'viewed');
                })->count();


                // =========================================================
                // 2. RECEIVED DOCUMENTS LOGIC (Ported from Controller)
                // =========================================================
                
                // Helper: Defined as a Closure inside the composer
                $filterPendingDocuments = function ($documents, $userId) {
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
                };

                // Define Access Log Constraint for Eager Loading (Optimization)
                $logConstraint = fn($q) => $q->where('user_id', $userId)->where('action', 'viewed');

                // --- MAIN COLLECTION ---
                $docs = collect();

                // 1️⃣ Direct recipient
                // Check if userOffice exists to prevent crash
                $directDocs = $userOffice ? $userOffice->receivedDocuments()
                    ->with(['documentType', 'toOffice', 'accessLogs' => $logConstraint])
                    ->get() : collect();

                // 2️⃣ Routing docs
                $routingDocs = Document::whereHas('routings', fn($q) => $q->where('user_id', $userId))
                    ->where('status', '!=', 'draft')
                    ->with(['documentType', 'fromOffice', 'routings', 'accessLogs' => $logConstraint])
                    ->get();
                $routingDocs = $filterPendingDocuments($routingDocs, $userId);

                // 3️⃣ Signatory docs
                $signatoryDocs = Document::whereHas('signatories', fn($q) => $q->where('user_id', $userId))
                    ->where('status', '!=', 'draft')
                    ->with(['documentType', 'fromOffice', 'signatories', 'accessLogs' => $logConstraint])
                    ->get();
                $signatoryDocs = $filterPendingDocuments($signatoryDocs, $userId);

                // 4️⃣ CF docs
                $cfDocs = Document::whereHas('cfs', fn($q) => $q->where('user_id', $userId))
                    ->where('status', '!=', 'draft')
                    ->with(['documentType', 'fromOffice', 'cfs', 'accessLogs' => $logConstraint])
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
                if ($userOffice && $userOffice->name === 'Administration') {
                    $presidentOfficeId = Office::whereRelation('users', 'position', 'University President')->value('id');
                    $presidentUserId = Office::whereRelation('users', 'position', 'University President')->value('head_id');
                    
                    if ($presidentOfficeId) {
                        $presidentDocs = Document::where(function ($q) {
                                $q->where('document_type_id', 3)
                                ->orWhere(function ($subQuery) {
                                    $subQuery->where('document_type_id', 1)
                                            ->where('status', '!=', 'draft'); 
                                });
                            })
                            ->when($user->role_id == 4, function ($query) {
                                $query->whereDoesntHave('routings.user', fn($q) => $q->where('office_id', 19));
                            }, function ($query) {
                                $query->whereHas('routings.user', fn($q) => $q->where('office_id', 19));
                            })
                            ->with(['documentType', 'fromOffice', 'signatories', 'routings.user', 'accessLogs' => $logConstraint])
                            ->get();

                        $presidentDocs = $filterPendingDocuments($presidentDocs, $presidentUserId);
                        $docs = $docs->merge($presidentDocs)->unique('id')->values();
                    }
                }

                // 6️⃣ Filter out certain doc types for President
                if ($user->position == 'University President') {
                    $docs = $docs->reject(fn($doc) => in_array($doc->document_type_id, [1, 3]));
                }

                // 7️⃣ Filter based on Status and Relationship
                $docs = $docs->filter(function ($doc) use ($userId) {
                    $isSignatory = $doc->signatories->contains('user_id', $userId);
                    $isRouting = $doc->routings->contains('user_id', $userId);
                    $isCf = $doc->cfs->contains('user_id', $userId) || $isRouting; // Logic from your snippet
                    $isRecipient = optional($doc->toOffice)->head_id == $userId;

                    // If user is both (signatory/routing) and (cf/recipient) → allow all
                    if (($isSignatory || $isRouting) && ($isCf || $isRecipient)) {
                        return true;
                    }

                    // If user is only CF or Recipient → allow only Approved/Distributed
                    if ($isCf || $isRecipient) {
                        return in_array($doc->status, ['Approved', 'Distributed']);
                    }

                    return true;
                });

                // *** FINAL CALCULATION FOR RECEIVED ***
                // We check if the eager-loaded accessLogs collection is empty.
                // If it's empty, the user has NOT viewed it.
                $unreadReceived = $docs->where(fn($doc) => $doc->accessLogs->isEmpty())->count();

                // 3. All Documents Logic (New)
                // This counts ALL unread internal documents (Intra + Others)
                // Note: If users are only allowed to see specific offices, add that ->where() here too.
                $unreadAll = Document::query()
                    ->whereDoesntHave('accessLogs', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('action', 'viewed');
                    })->count();
            }

            // Pass all variables to the view
            $view->with('unreadExternalCount', $unreadExternal);
            $view->with('unreadReceivedCount', $unreadReceived);
            $view->with('unreadAllCount', $unreadAll); // <--- PASS TO VIEW
        });
    }
}
