<style>
<<<<<<< HEAD

  .dashboard-card {
    background-color: #ffffff !important;
    border-color: #d1d5db !important;
  }

  .dashboard-card h3,
  .dashboard-card .card-value {
    color: #111827 !important;
  }

  .dashboard-card .card-meta {
    color: #6b7280 !important;
  }

  .dark .dashboard-card {
    background-color: #0f172a !important;
    border-color: #334155 !important;
  }

  .dark .dashboard-card h3,
  .dark .dashboard-card .card-value {
    color: #f8fafc !important;
  }

  .dark .dashboard-card .card-meta {
    color: #cbd5e1 !important;
  }

  .dark #chartdiv,
  .dark #chartdiv2,
  html.dark #chartdiv,
  html.dark #chartdiv2 {
    background-color: #18181b !important;
    box-shadow: 0 4px 8px rgba(255, 255, 255, 0.05);
  }

  .dark .chart-label,
  .dark .history-header,
  .dark .history-title,
  .dark .history-desc,
  .dark .history-time {
    color: #f8fafc;
  }

  .dark .history-item {
    border-bottom-color: #1f2937;
  }

  .dark .history-icon {
    background-color: #1e293b;
=======
   body {
    background-color: #f6f6f6;
  }
  .card-body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 50vh;
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
  }

  /* First chart styles */
  #chartdiv {
<<<<<<< HEAD
    width: 70%;
=======
    width: 80%;
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    height: 480px;
    max-height: 500px;
    min-height: 500px;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    padding: 8px;
    position: relative; /* Needed for positioning the label inside */
  }

  .chart-label {
    position: absolute;
<<<<<<< HEAD
    top: 10px;
=======
    top: 4px;
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    left: 50%;
    transform: translateX(-50%);
    font-size: 14px;
    color: #555;
<<<<<<< HEAD
    font-weight: 600;
    z-index: 10;
    pointer-events: none;
=======
    font-weight: 500;
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
  }

  /* Second chart styles */
  #chartdiv2 {
    width: 28%; /* Narrower than chartdiv */
    height: 420px; /* Shorter than chartdiv */
    max-height: 420px;
    min-height: 420px;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    padding: 15px;
    display: flex;
    flex-direction: column;
  }

  .charts-wrapper {
    display: flex;
    justify-content: space-between;
    width: 100%;
    gap: 2%;
    margin-top: 0px; /* Reduce and Remove the top margin completely */
  }

  /* Document History Panel Styles */
  .history-header {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #333;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
  }

  .history-container {
    flex: 1;
    overflow-y: auto;
    padding-right: 5px;
  }

  .history-item {
    display: flex;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
  }

  .history-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
  }

  .history-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e3f2fd;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
<<<<<<< HEAD
    overflow: hidden;
    border: 2px solid #dbeafe;
  }

  .history-avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .history-avatar-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #2563eb;
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
=======
  }

  .history-icon i {
    color: #1976d2;
    font-size: 18px;
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
  }

  .history-content {
    flex: 1;
  }

  .history-title {
    font-weight: 500;
    margin-bottom: 3px;
    color: #333;
  }

  .history-desc {
    font-size: 13px;
    color: #666;
    margin-bottom: 5px;
  }

<<<<<<< HEAD
  .history-action {
    font-weight: 700;
  }

  .history-action--viewed {
    color: #2563eb;
  }

  .history-action--sent {
    color: #16a34a;
  }

=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
  .history-time {
    font-size: 12px;
    color: #999;
  }

<<<<<<< HEAD
  .dark .history-icon {
    background-color: #1e3a5f;
    border-color: #334155;
  }

=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
  /* Scrollbar styling */
  .history-container::-webkit-scrollbar {
    width: 6px;
  }

  .history-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .history-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
  }

  .history-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }

  @media (max-width: 768px) {
    .charts-wrapper {
      flex-direction: column;
      gap: 20px;
    }
    #chartdiv, #chartdiv2 {
      width: 100% !important;
      height: 400px !important;
    }
  }

  @media (max-width: 480px) {
    #chartdiv, #chartdiv2 {
      height: 350px !important;
    }
  }
</style>

<<<<<<< HEAD
@php
    $totalDocsCount = \App\Models\Document::count();
    $extDocsCount = \App\Models\ExternalDocument::count();
    $grandTotalDocs = $totalDocsCount + $extDocsCount;
    
    $sentDocsCount = \App\Models\Document::where('status', 'Sent')->count();
    $approvedDocsCount = \App\Models\Document::where('status', 'Approved')->count();
    $rejectedDocsCount = \App\Models\Document::where('status', 'Rejected')->count();
    $draftDocsCount = \App\Models\Document::where('status', 'Draft')->count();

    $officesCount = \App\Models\Office::count();
    $usersCount = \App\Models\User::count();

    $statusTotal = max(1, $totalDocsCount);
    $approvedPct = round(($approvedDocsCount / $statusTotal) * 100, 1);
    $sentPct = round(($sentDocsCount / $statusTotal) * 100, 1);
    $draftPct = round(($draftDocsCount / $statusTotal) * 100, 1);
    $rejectedPct = round(($rejectedDocsCount / $statusTotal) * 100, 1);

    // Document Classifications Breakdown from Database
    $docTypes = \App\Models\DocumentType::withCount('documents')->get();
    $docTypeTotal = max(1, $docTypes->sum('documents_count'));

    // Top Active Offices from Database
    $topOffices = \App\Models\Office::withCount(['sentDocuments', 'receivedDocuments'])
        ->get()
        ->map(function ($off) {
            $off->total_activity = $off->sent_documents_count + $off->received_documents_count;
            return $off;
        })
        ->sortByDesc('total_activity')
        ->take(4);

    // Show user activity for documents that were viewed or sent.
    $historyLogs = \App\Models\DocumentLog::with(['document', 'user'])
        ->whereIn('action', ['viewed', 'sent'])
        ->latest()
        ->take(7)
        ->get();

    // Yearly Data mapping from database for amCharts
    $currentYear = (int) date('Y');
    $startYear = 2022;

    $yearlyDocsMap = \App\Models\Document::whereNotNull('created_at')
        ->get(['created_at'])
        ->groupBy(fn($doc) => $doc->created_at->format('Y'))
        ->map(fn($group) => $group->count());

    $yearlyOfficesMap = \App\Models\Office::whereNotNull('created_at')
        ->get(['created_at'])
        ->groupBy(fn($office) => $office->created_at->format('Y'))
        ->map(fn($group) => $group->count());

    $yearlyUsersMap = \App\Models\User::whereNotNull('created_at')
        ->get(['created_at'])
        ->groupBy(fn($user) => $user->created_at->format('Y'))
        ->map(fn($group) => $group->count());

    $yearlyChartData = [
        'Documents' => [],
        'Offices' => [],
        'Users' => []
    ];

    for ($y = $startYear; $y <= $currentYear; $y++) {
        $yearStr = (string)$y;
        $yearlyChartData['Documents'][$yearStr] = $yearlyDocsMap->get($yearStr, 0);
        $yearlyChartData['Offices'][$yearStr] = $yearlyOfficesMap->get($yearStr, 0);
        $yearlyChartData['Users'][$yearStr] = $yearlyUsersMap->get($yearStr, 0);
    }
@endphp

<x-layouts.app title="Dashboard">
  <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div>
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Document Tracking Analytics</h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Real-time overview of document movement, office turnaround, and system activity across university offices.</p>
        </div>
        <div class="flex items-center gap-2">
          {{-- FIX: Hide Create Document button for Administrators as well --}}
          @if (auth()->user()?->position != 'Staff' && auth()->user()?->position != 'Administrator')
            <flux:button href="{{ route('documents.create-document') }}" variant="primary" icon="plus" size="sm">
              Create Document
            </flux:button>
          @endif
        </div>
      </div>
    </div>

    <!-- 5 KPI Cards -->
    <div class="grid auto-rows-min gap-4 md:grid-cols-5">
      <!-- Card 1: Total Documents -->
      <div class="dashboard-card relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300">
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 class="card-title" style="font-size: 16px; margin-bottom: 2px;">Total Documents</h3>
            <p class="card-value" style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">{{ number_format($grandTotalDocs) }}</p>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 16px; top: 16px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
            <img src="{{ asset('assets/img/doc.gif') }}" alt="Document Icon" style="width: 28px; height: 28px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #4CAF50;">Live Database Record</p>
        </div>
      </div>

      <!-- Card 2: Active Users -->
      <div class="dashboard-card relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300">
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 class="card-title" style="font-size: 16px; margin-bottom: 2px;">System Users</h3>
            <p class="card-value" style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">{{ number_format($usersCount) }}</p>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 16px; top: 16px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
            <img src="{{ asset('assets/img/team.gif') }}" alt="Users Icon" style="width: 28px; height: 28px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p class="card-meta" style="font-size: 13px; color: #4CAF50;">Registered Users</p>
        </div>
      </div>

      <!-- Card 3: Active Routing / Sent -->
      <div class="dashboard-card relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300">
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 class="card-title" style="font-size: 16px; margin-bottom: 2px;">In-Routing</h3>
            <p class="card-value" style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">{{ number_format($sentDocsCount) }}</p>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 16px; top: 16px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
            <img src="{{ asset('assets/img/box.gif') }}" alt="Routing Icon" style="width: 28px; height: 28px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p class="card-meta" style="font-size: 13px; color: #35a7ff;">Active Movement</p>
        </div>
      </div>

      <!-- Card 4: ZPPSU Offices -->
      <div class="dashboard-card relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300">
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 class="card-title" style="font-size: 16px; margin-bottom: 2px;">ZPPSU Offices</h3>
            <p class="card-value" style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">{{ number_format($officesCount) }}</p>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 16px; top: 16px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
            <img src="{{ asset('assets/img/building.gif') }}" alt="Building Icon" style="width: 28px; height: 28px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p class="card-meta" style="font-size: 13px; color: #f1c50f;">Academic & Admin units</p>
        </div>
      </div>

      <!-- Card 5: Approved Documents -->
      <div class="dashboard-card relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300">
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 class="card-title" style="font-size: 16px; margin-bottom: 2px;">Approved</h3>
            <p class="card-value" style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">{{ number_format($approvedDocsCount) }}</p>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 16px; top: 16px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
            <img src="{{ asset('assets/img/graph.gif') }}" alt="Approved Icon" style="width: 28px; height: 28px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #4CAF50;">Successfully Processed</p>
=======
<x-layouts.app title="Dashboard">
  <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="grid auto-rows-min gap-4 md:grid-cols-5">
      <!-- Your 5 cards here (same as before) -->
      <!-- Blue Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Documents</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">350,897</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/13099/13099823.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #4CAF50;">↑ 3.48% from last month</p>
        </div>
      </div>

      <!-- Green Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Users</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">266</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/7211/7211817.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #F44336;">↓ 1.26% from last month</p>
        </div>
      </div>

      <!-- Orange Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Files</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">350,897</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/15309/15309697.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #35a7ff;">↑ 3.48% from last month</p>
        </div>
      </div>

      <!-- Red Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Offices</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">281</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/8722/8722580.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #f1c50f;">↓ 1.02% from last month</p>
        </div>
      </div>

      <!-- Yellow Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Recieved</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">350,897</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/7211/7211797.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #4CAF50;">↑ 3.48% from last month</p>
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
        </div>
      </div>
    </div>

    <!-- Charts Container -->
    <div class="chart-container">
      <div class="charts-wrapper">
        <div id="chartdiv">
<<<<<<< HEAD
          <div class="chart-label">ZPPSU - DTS Document Tracking Analytics</div>
          <div id="chartcanvas" style="width: 100%; height: 100%;"></div>
        </div>
        <div id="chartdiv2">
          <div class="history-header">Document History</div>
          <div class="history-container">
            @if($historyLogs->isNotEmpty())
              @foreach($historyLogs as $log)
                @php
                  $activityUser = $log->user;
                  $activityAction = strtolower($log->action) === 'sent' ? 'sent' : 'viewed';
                  $activityLabel = $activityAction === 'sent' ? 'Sent' : 'Viewed';
                  $documentNumber = $log->document?->document_number ?? 'Document';
                @endphp
                <div class="history-item">
                  <div class="history-icon">
                    @if($activityUser?->avatar_url)
                      <img src="{{ $activityUser->avatar_url }}" alt="{{ $activityUser->name }}'s profile photo" class="history-avatar">
                    @else
                      <span class="history-avatar-fallback" aria-label="{{ $activityUser?->name ?? 'Unknown user' }}">
                        {{ $activityUser?->initials() ?? '?' }}
                      </span>
                    @endif
                  </div>
                  <div class="history-content">
                    <div class="history-title">{{ $activityUser?->name ?? 'Unknown user' }}</div>
                    <div class="history-desc">
                      <span class="history-action history-action--{{ $activityAction }}">{{ $activityLabel }}</span>
                      a document — {{ $documentNumber }}{{ $log->document?->subject ? ': ' . $log->document->subject : '' }}
                    </div>
                    <div class="history-time">{{ $log->created_at->diffForHumans() }}</div>
                  </div>
                </div>
              @endforeach
            @else
              <div class="p-6 text-center text-xs text-gray-500">No viewed or sent document activity recorded yet</div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- ZPPSU Document Tracking Advanced Analytics Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
      <!-- Widget 1: Document Classification Breakdown -->
      <div class="analytics-card bg-white dark:bg-zinc-800 rounded-xl border border-gray-200 dark:border-zinc-700 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">Document Classifications</h3>
          <span class="text-xs px-2 py-1 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded font-medium">Database Types</span>
        </div>
        <div class="space-y-4">
          @forelse($docTypes as $type)
            @php
                $pct = round(($type->documents_count / $docTypeTotal) * 100, 1);
            @endphp
            <div>
              <div class="flex justify-between text-xs font-medium text-gray-700 dark:text-zinc-300 mb-1">
                <span>{{ $type->name }} ({{ $type->abbreviation ?? 'DOC' }})</span>
                <span>{{ $pct }}% ({{ number_format($type->documents_count) }})</span>
              </div>
              <div class="w-full bg-gray-100 dark:bg-zinc-700 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ max(2, $pct) }}%"></div>
              </div>
            </div>
          @empty
            <div class="text-xs text-gray-500">No document types configured in database</div>
          @endforelse
        </div>
      </div>

      <!-- Widget 2: Document Processing Efficiency -->
      <div class="analytics-card bg-white dark:bg-zinc-800 rounded-xl border border-gray-200 dark:border-zinc-700 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">Routing & Approval Status</h3>
          <span class="text-xs px-2 py-1 bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded font-medium">{{ $approvedPct }}% Approved</span>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div class="p-3 bg-gray-50 dark:bg-zinc-700/50 rounded-lg text-center">
            <p class="text-xs text-gray-500 dark:text-zinc-400">Total Tracked</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalDocsCount) }}</p>
            <span class="text-[10px] text-green-600 dark:text-green-400">System Records</span>
          </div>
          <div class="p-3 bg-gray-50 dark:bg-zinc-700/50 rounded-lg text-center">
            <p class="text-xs text-gray-500 dark:text-zinc-400">Active Routing</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ number_format($sentDocsCount) }}</p>
            <span class="text-[10px] text-blue-600 dark:text-blue-400">In Transit</span>
          </div>
        </div>
        <div class="space-y-3">
          <div class="flex items-center justify-between text-xs">
            <span class="flex items-center gap-2 text-gray-700 dark:text-zinc-300">
              <span class="size-2 rounded-full bg-green-500"></span> Approved & Released
            </span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($approvedDocsCount) }}</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="flex items-center gap-2 text-gray-700 dark:text-zinc-300">
              <span class="size-2 rounded-full bg-blue-500"></span> In Routing / Under Review
            </span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($sentDocsCount) }}</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="flex items-center gap-2 text-gray-700 dark:text-zinc-300">
              <span class="size-2 rounded-full bg-amber-500"></span> Saved Drafts
            </span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($draftDocsCount) }}</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="flex items-center gap-2 text-gray-700 dark:text-zinc-300">
              <span class="size-2 rounded-full bg-red-500"></span> Revisions / Returned
            </span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($rejectedDocsCount) }}</span>
          </div>
        </div>
      </div>

      <!-- Widget 3: Top Active ZPPSU Units -->
      <div class="analytics-card bg-white dark:bg-zinc-800 rounded-xl border border-gray-200 dark:border-zinc-700 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">Top Active ZPPSU Units</h3>
          <span class="text-xs px-2 py-1 bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded font-medium">Database Units</span>
        </div>
        <div class="space-y-3">
          @forelse($topOffices as $office)
            <div class="flex items-center justify-between p-2 rounded hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
              <div class="flex items-center gap-2.5">
                <div class="size-7 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs">
                  {{ strtoupper(substr($office->abbreviation ?? $office->name, 0, 3)) }}
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-900 dark:text-white line-clamp-1">{{ $office->name }}</p>
                  <p class="text-[10px] text-gray-500 dark:text-zinc-400">{{ $office->abbreviation ?? ($office->office_type ?? 'Office Unit') }}</p>
                </div>
              </div>
              <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded whitespace-nowrap">{{ number_format($office->total_activity) }} docs</span>
            </div>
          @empty
            <div class="text-xs text-gray-500">No office units registered in database yet</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
=======
         <div class="chart-label">ZPPSU - DTS Document Trackig Analytics</div>
          </div>
       <div id="chartdiv2">
  <div class="history-header">Document History</div>
  <div class="history-container">
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/4138/4138774.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">New Document Created</div>
        <div class="history-desc">Project Proposal.docx</div>
        <div class="history-time">Just now</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/3006/3006899.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Uploaded</div>
        <div class="history-desc">Annual Report 2025.pdf</div>
        <div class="history-time">2 minutes ago</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/16683/16683419.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Edited</div>
        <div class="history-desc">Project Proposal.pptx</div>
        <div class="history-time">1 hour ago</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
         <img src="https://cdn-icons-png.flaticon.com/128/16683/16683439.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Shared</div>
        <div class="history-desc">Budget Plan.xlsx with Finance Team</div>
        <div class="history-time">3 hours ago</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/14178/14178961.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Approved</div>
        <div class="history-desc">Contract Agreement.docx</div>
        <div class="history-time">Yesterday, 2:45 PM</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/4138/4138853.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Downloaded</div>
        <div class="history-desc">Meeting Minutes.pdf by John Doe</div>
        <div class="history-time">Yesterday, 10:30 AM</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/11107/11107601.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Archived</div>
        <div class="history-desc">Q1 Financial Report.xlsx</div>
        <div class="history-time">May 28, 2025</div>
      </div>
    </div>
  </div>
</div>
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045

  <!-- amCharts Scripts -->
  <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

<<<<<<< HEAD

  <!-- Chart Script -->
  <script>
    var _chartInitTimer = null;

    function safeInitDashboardChart() {
      if (_chartInitTimer) {
        clearTimeout(_chartInitTimer);
      }
      _chartInitTimer = setTimeout(function() {
        initDashboardChart();
      }, 50);
    }

    function initDashboardChart() {
      var divId = "chartcanvas";
      var container = document.getElementById(divId);
      if (!container) return;

      if (typeof am5 !== 'undefined') {
        if (window._dashboardChartRoot) {
          try { window._dashboardChartRoot.dispose(); } catch(e) {}
          window._dashboardChartRoot = null;
        }

        am5.array.each(am5.registry.rootElements, function(root) {
          if (root) {
            try { root.dispose(); } catch(e) {}
          }
        });

        container.innerHTML = "";

        createChart(divId);
      }
    }

    if (typeof am5 !== 'undefined') {
      am5.ready(safeInitDashboardChart);
    }

    document.addEventListener('livewire:navigated', safeInitDashboardChart);

    // Watch for dark mode changes dynamically
    if (!window._chartDarkObserver) {
      window._chartDarkObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.attributeName === 'class') {
            safeInitDashboardChart();
          }
        });
      });
      window._chartDarkObserver.observe(document.documentElement, { attributes: true });
    }

    function createChart(divId) {
      window._dashboardChartRoot = am5.Root.new(divId);
      var root = window._dashboardChartRoot;
=======
  <!-- Font Awesome for icons -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <!-- Chart Script -->
  <script>
    am5.ready(function() {
      // Create first chart
      createChart("chartdiv");
    });

    function createChart(divId) {
      var root = am5.Root.new(divId);
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
      root._logo.set("scale", 0.0);
      root._logo.set("paddingTop", 1);
      root._logo.set("paddingRight", 1);
      root._logo.set("opacity", 0.01);
      root.setThemes([am5themes_Animated.new(root)]);

<<<<<<< HEAD
      var isDarkMode = document.documentElement.classList.contains('dark');
      var textColor = isDarkMode ? am5.color(0xFFFFFF) : am5.color(0x475569);
      var gridColor = isDarkMode ? am5.color(0x3f3f46) : am5.color(0xE2E8F0);
      var tooltipBg = isDarkMode ? am5.color(0x27272a) : am5.color(0xFFFFFF);
      var tooltipText = isDarkMode ? am5.color(0xFFFFFF) : am5.color(0x000000);
      var chartBackground = isDarkMode ? am5.color(0x18181b) : am5.color(0xFFFFFF);

=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
      var chart = root.container.children.push(
        am5xy.XYChart.new(root, {
          panX: false,
          panY: false,
          wheelX: "none",
          wheelY: "none",
<<<<<<< HEAD
          paddingLeft: 0,
          background: am5.RoundedRectangle.new(root, {
            fill: chartBackground,
            fillOpacity: 1
          })
        })
      );

      root.interfaceColors.set("text", textColor);
      root.interfaceColors.set("grid", gridColor);
      root.interfaceColors.set("background", chartBackground);
      root.interfaceColors.set("alternativeBackground", chartBackground);

      var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
      cursor.lineY.set("visible", false);
      cursor.lineX.setAll({ stroke: gridColor, strokeOpacity: 0.3 });
=======
          paddingLeft: 0
        })
      );

      var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
      cursor.lineY.set("visible", false);
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045

      var xRenderer = am5xy.AxisRendererX.new(root, {
        minGridDistance: 30,
        minorGridEnabled: true
      });
      xRenderer.labels.template.setAll({ text: "{realName}" });

      var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
        maxDeviation: 0,
        categoryField: "category",
        renderer: xRenderer,
        tooltip: am5.Tooltip.new(root, { labelText: "{realName}" })
      }));

      var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
        maxDeviation: 0.3,
        renderer: am5xy.AxisRendererY.new(root, {})
      }));

      var yAxis2 = chart.yAxes.push(am5xy.ValueAxis.new(root, {
        maxDeviation: 0.3,
        syncWithAxis: yAxis,
        renderer: am5xy.AxisRendererY.new(root, { opposite: true })
      }));

<<<<<<< HEAD
      xRenderer.labels.template.setAll({ fill: textColor, text: "{realName}" });
      xRenderer.grid.template.setAll({ stroke: gridColor });
      xRenderer.ticks.template.setAll({ stroke: gridColor });
      yAxis.get("renderer").labels.template.setAll({ fill: textColor });
      yAxis.get("renderer").grid.template.setAll({ stroke: gridColor });
      yAxis2.get("renderer").labels.template.setAll({ fill: textColor });
      yAxis2.get("renderer").grid.template.setAll({ stroke: gridColor });

=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
      var series = chart.series.push(am5xy.ColumnSeries.new(root, {
        name: "Series 1",
        xAxis: xAxis,
        yAxis: yAxis2,
        valueYField: "value",
        sequencedInterpolation: true,
        categoryXField: "category",
        tooltip: am5.Tooltip.new(root, {
<<<<<<< HEAD
          labelText: "{provider} {realName}: {valueY}",
          getFillFromSprite: false,
          background: am5.RoundedRectangle.new(root, {
            fill: tooltipBg,
            fillOpacity: 0.95
          }),
          labelTextColor: tooltipText
=======
          labelText: "{provider} {realName}: {valueY}"
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
        })
      }));

      series.columns.template.setAll({
        fillOpacity: 0.9,
        strokeOpacity: 0
      });

      series.columns.template.adapters.add("fill", (fill, target) =>
        chart.get("colors").getIndex(series.columns.indexOf(target))
      );
      series.columns.template.adapters.add("stroke", (stroke, target) =>
        chart.get("colors").getIndex(series.columns.indexOf(target))
      );

      var lineSeries = chart.series.push(am5xy.LineSeries.new(root, {
        name: "Series 2",
        xAxis: xAxis,
        yAxis: yAxis,
        valueYField: "quantity",
        sequencedInterpolation: true,
        stroke: chart.get("colors").getIndex(13),
        fill: chart.get("colors").getIndex(13),
        categoryXField: "category",
<<<<<<< HEAD
        tooltip: am5.Tooltip.new(root, {
          labelText: "{valueY}",
          getFillFromSprite: false,
          background: am5.RoundedRectangle.new(root, {
            fill: tooltipBg,
            fillOpacity: 0.95
          }),
          labelTextColor: tooltipText
        })
=======
        tooltip: am5.Tooltip.new(root, { labelText: "{valueY}" })
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
      }));

      lineSeries.strokes.template.set("strokeWidth", 2);

      lineSeries.bullets.push(function() {
        return am5.Bullet.new(root, {
          locationY: 1,
          sprite: am5.Circle.new(root, {
            radius: 5,
            fill: lineSeries.get("fill")
          })
        });
      });

      lineSeries.events.on("datavalidated", function() {
        am5.array.each(lineSeries.dataItems, function(dataItem) {
          dataItem.set("locationX",
            dataItem.dataContext.count % 2 === 0 ? 0 : 0.5
          );
        });
      });

      var chartData = [];

<<<<<<< HEAD
      var currentYear = new Date().getFullYear();
      var startYear = 2022;
      var dbDocsCount = {{ $totalDocsCount }};

      function generateYearData(baseMultiplier, defaultQuantity) {
        var res = { quantity: defaultQuantity };
        for (var y = startYear; y <= currentYear; y++) {
          var yStr = y.toString();
          if (y === currentYear && dbDocsCount > 0) {
            res[yStr] = dbDocsCount;
          } else {
            res[yStr] = Math.floor(10 + (y - startYear) * baseMultiplier + ((y % 3) + 1) * 5);
          }
        }
        return res;
      }

      var data = {
        "Documents": generateYearData(12, {{ $grandTotalDocs > 0 ? $grandTotalDocs : 430 }}),
        "Offices": generateYearData(5, {{ $officesCount > 0 ? $officesCount : 210 }}),
        "Departments": generateYearData(6, 265),
        "Office Heads": generateYearData(4, 98)
=======
      var data = {
        "Documents": {
          "2022": 5,
          "2023": 20,
          "2024": 15,
          "2025": 25,
          quantity: 430
        },
        "Offices": {
          "2020": 15,
          "2022": 21,
          "2024": 18,
          "2025": 22,
          quantity: 210
        },
        "Departments": {
          "2021": 25,
          "2022": 11,
          "2023": 17,
          "2024": 30,
          "2025": 20,
          quantity: 265
        },
        "Office Heads": {
          "2022": 12,
          "2023": 15,
          "2024": 10,
          "2025": 8,
          quantity: 98
        }
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
      };

      for (var providerName in data) {
        var providerData = data[providerName];
        var tempArray = [];
        var count = 0;

        for (var itemName in providerData) {
          if (itemName !== "quantity") {
            count++;
            tempArray.push({
              category: providerName + "_" + itemName,
              realName: itemName,
              value: providerData[itemName],
              provider: providerName
            });
          }
        }

<<<<<<< HEAD
        // Sort chronologically by year (2022, 2023, 2024, 2025, 2026, 2027...)
        tempArray.sort((a, b) => parseInt(a.realName) - parseInt(b.realName));
=======
        tempArray.sort((a, b) => a.value - b.value);
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
        var midIndex = Math.floor(count / 2);
        tempArray[midIndex].quantity = providerData.quantity;
        tempArray[midIndex].count = count;

        am5.array.each(tempArray, item => chartData.push(item));

        var range = xAxis.makeDataItem({});
        xAxis.createAxisRange(range);
        range.set("category", tempArray[0].category);
        range.set("endCategory", tempArray[tempArray.length - 1].category);
        range.get("label").setAll({
          text: tempArray[0].provider,
          dy: 30,
          fontWeight: "bold",
          tooltipText: tempArray[0].provider
        });
        range.get("tick").setAll({ visible: true, strokeOpacity: 1, length: 50, location: 0 });
        range.get("grid").setAll({ strokeOpacity: 1 });
      }

      var finalRange = xAxis.makeDataItem({});
      xAxis.createAxisRange(finalRange);
      finalRange.set("category", chartData[chartData.length - 1].category);
      finalRange.get("tick").setAll({ visible: true, strokeOpacity: 1, length: 50, location: 1 });
      finalRange.get("grid").setAll({ strokeOpacity: 1, location: 1 });

      xAxis.data.setAll(chartData);
      series.data.setAll(chartData);
      lineSeries.data.setAll(chartData);

      series.appear(1000);
      chart.appear(1000, 100);
    }
  </script>
</x-layouts.app>