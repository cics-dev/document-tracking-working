<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
    :root { --primary: #800000; --secondary: #ffc107; --light-gray: #f5f5f5; --dark-gray: #757575; --success: #4caf50; --warning: #ff9800; --info: #2196f3; }
    .tracking-container { background-color: white; border-radius: 8px; padding: 25px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
    .tracking-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid #eee; }
    .order-id { font-size: 18px; font-weight: 600; } .order-id span { color: var(--primary); }
    .tracking-progress { display: flex; justify-content: space-between; position: relative; margin: 40px 0; }
    .progress-line { position: absolute; height: 3px; background-color: #e0e0e0; top: 15px; left: 0; right: 0; z-index: 1; }
    .progress-line-active { background-color: var(--primary); width: 0%; transition: width 0.5s ease; }
    .status-step { display: flex; flex-direction: column; align-items: center; z-index: 2; flex: 1; text-align: center; padding: 0 5px; }
    .status-icon { width: 32px; height: 32px; border-radius: 50%; background-color: #e0e0e0; display: flex; justify-content: center; align-items: center; margin-bottom: 10px; color: white; transition: all 0.3s ease; }
    .status-icon.active { background-color: var(--primary); transform: scale(1.1); }
    .status-icon.completed { background-color: var(--success); } 
    .status-icon.rejected { background-color: #dc3545; }
    .status-label { font-size: 13px; text-align: center; color: var(--dark-gray); transition: all 0.3s ease; font-weight: 500; }
    .status-label.active { color: var(--primary); font-weight: 600; }
    .status-date { font-size: 11px; color: var(--dark-gray); margin-top: 5px; transition: all 0.3s ease; }
    .status-date.active { color: var(--primary); font-weight: 500; }
    .courier-info { background-color: var(--light-gray); padding: 15px; border-radius: 8px; margin: 30px 0; display: flex; justify-content: space-between; align-items: center; }
    .courier-name { font-weight: 600; } .tracking-link { color: var(--primary); text-decoration: none; font-weight: 500; }
    .status-updates h3 { margin-top: 20px; }
    .update-card { display: flex; padding: 15px 0; border-bottom: 1px solid #eee; transition: background-color 0.2s; }
    .update-card:hover { background-color: #f9f9f9; } .update-icon { margin-right: 15px; color: var(--primary); }
    .update-time { color: var(--dark-gray); font-size: 13px; }
    .action-buttons { text-align: right; margin-top: 20px; display: flex; justify-content: space-between; }
    .action-buttons .btn-group { display: flex; gap: 10px; }
    .btn { padding: 10px 20px; border-radius: 4px; font-weight: 500; cursor: pointer; border: none; transition: all 0.3s ease; }
    .btn-primary { background-color: var(--primary); color: white; } .btn-primary:hover { background-color: #600000; }
    .btn-outline { background-color: transparent; border: 1px solid var(--primary); color: var(--primary); } .btn-outline:hover { background-color: rgba(128, 0, 0, 0.1); }
    .document-details { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
    .detail-card { background-color: var(--light-gray); padding: 15px; border-radius: 6px; min-height: 80px; display: flex; flex-direction: column; justify-content: center; }
    .detail-label { font-size: 13px; color: var(--dark-gray); margin-bottom: 5px; } .detail-value { font-weight: 500; font-size: 14px; }
    .alert-banner { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; display: flex; align-items: center; }
    .alert-banner i { margin-right: 10px; color: var(--warning); }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-left: 10px; text-transform: capitalize; }
    .status-badge.draft { background-color: #f1f5f9; color: #475569; }
    .status-badge.sent { background-color: #fff3e0; color: var(--warning); }
    .status-badge.in.process, .status-badge.in_process { background-color: #e0f2fe; color: var(--info); }
    .status-badge.approved, .status-badge.completed { background-color: #e8f5e9; color: var(--success); } 
    .status-badge.rejected, .status-badge.returned { background-color: #ffebee; color: #f44336; }
    .status-text { color: #3a3838ff; font-size: 0.875rem; font-weight: 500; }
    .status-value { font-size: 0.875rem; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 0.375rem; margin-left: 0.5rem; display: inline-block; transition: all 0.3s ease; }
    @media (max-width: 768px) {
        .document-details { grid-template-columns: repeat(2, 1fr); }
        .tracking-progress { flex-direction: column; align-items: flex-start; margin: 20px 0; }
        .status-step { flex-direction: row; margin-bottom: 15px; width: 100%; text-align: left; }
        .status-icon { margin-right: 15px; margin-bottom: 0; } .progress-line { display: none; }
        .action-buttons { flex-direction: column; gap: 10px; } .action-buttons .btn-group { width: 100%; justify-content: space-between; }
    }
    @media (max-width: 480px) { .document-details { grid-template-columns: 1fr; } }
</style>

@php
    $rawStatus = $document->status ?? 'Draft';
    $displayStatus = str($rawStatus)->headline();
    $steps = $document->steps->sortBy('sequence')->values();
    $rejectedStepIndex = $steps->search(
        fn($step) => in_array(strtolower($step->status), ['rejected', 'returned'], true)
    );
@endphp

<div class="container">
    <h1 class="text-2xl font-bold mb-4">Document Tracking</h1>
    <div class="tracking-container">
        <div class="tracking-header">
            <div class="order-id">Document Reference: <span id="document-number">{{ $document->document_number }}</span></div>
            <div class="status-badge {{ Str::slug($rawStatus) }}" id="status-badge">{{ $displayStatus }}</div>
        </div>

        <div class="document-details">
            <div class="detail-card">
                <div class="detail-label">Date Created</div>
                <div class="detail-value">{{ $document->created_at->format('M d, Y') }}</div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Document Type</div>
                <div class="detail-value">{{ $document->documentType->name ?? ($document->type ?? 'N/A') }}</div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Current Status</div>
                <div class="detail-value" id="current-status-text">{{ $displayStatus }}</div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Overall Progress</div>
                <div class="detail-value">
                    @php
                        $totalSteps = $steps->count();
                        $completedSteps = $steps->filter(fn($s) => !empty($s->processed_at))->count();
                        $percent = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
                        if ($rawStatus === 'Approved' || $rawStatus === 'Completed') $percent = 100;
                    @endphp
                    {{ $percent }}% Complete
                </div>
            </div>
        </div>

        @if($steps->isNotEmpty())
        <div class="tracking-progress">
            <div class="progress-line">
                <div class="progress-line-active" style="width: {{ $percent }}%;"></div>
            </div>
            @foreach($steps as $index => $step)
                @php
                    $isProcessed = !empty($step->processed_at);
                    $isRejected = in_array(strtolower($step->status), ['rejected', 'returned'], true);
                    $isPending = !$isProcessed && !$isRejected;
                    $isAfterRejected = $rejectedStepIndex !== false && $index > $rejectedStepIndex;
                    
                    $firstPending = $steps->first(fn($candidate) => empty($candidate->processed_at));
                    $isActive = $rejectedStepIndex === false && $isPending && $firstPending?->id === $step->id;

                    $iconClass = 'fa-clock';
                    $stepStateClass = '';
                    if ($isRejected) {
                        $iconClass = 'fa-times';
                        $stepStateClass = 'rejected';
                    } elseif ($isAfterRejected) {
                        $iconClass = null;
                    } elseif ($isProcessed) {
                        $iconClass = 'fa-check';
                        $stepStateClass = 'completed';
                    } elseif ($isActive) {
                        $iconClass = 'fa-hourglass-half';
                        $stepStateClass = 'active';
                    }
                @endphp
                <div class="status-step">
                    <div class="status-icon {{ $stepStateClass }}">
                        @if($iconClass)
                            <i class="fas {{ $iconClass }}"></i>
                        @endif
                    </div>
                    <div class="status-label {{ $isActive ? 'active' : '' }}">{{ $step->active_user?->office?->abbreviation ?? 'Unassigned' }}</div>
                    <div class="status-date {{ $isActive ? 'active' : '' }}">{{ $step->step_label }}</div>
                    <div class="status-date {{ $isActive ? 'active' : '' }}">
                        @if($isRejected)
                            Rejected
                        @elseif($isProcessed)
                            {{ \Carbon\Carbon::parse($step->processed_at)->format('M d, h:i A') }}
                        @else
                            Pending
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        <div class="status-updates" style="margin-top: 30px;">
            <h3>Document Activity Logs</h3>
            <div id="activity-logs">
                @if ($document->logs && $document->logs->count() > 0)
                    @foreach ($document->logs->sortByDesc('created_at') as $log)
                        <div class="update-card">
                            <div class="update-icon"><i class="fas fa-info-circle"></i></div>
                            <div>
                                <div>{{ $log->description ?? ucfirst($log->action) }}</div>
                                <div class="update-time">{{ $log->created_at->format('F d, Y h:i A') }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="update-card">
                        <div class="update-icon"><i class="fas fa-info-circle"></i></div>
                        <div>
                            <div>No activity logs available</div>
                            <div class="update-time">-</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="action-buttons" style="margin-top: 30px;">
            <div class="btn-group">
                <button class="btn btn-outline" id="share-btn"><i class="fas fa-share-alt"></i> Share Tracking</button>
                <button class="btn btn-outline" id="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Summary</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('share-btn')?.addEventListener('click', () => {
            const url = window.location.href;
            if (navigator.share) {
                navigator.share({ title: 'Document Tracking', url: url });
            } else {
                navigator.clipboard.writeText(url).then(() => alert('Tracking link copied to clipboard!'));
            }
        });
    });
</script>
