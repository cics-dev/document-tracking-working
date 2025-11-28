<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
    :root { --primary: #800000; --secondary: #ffc107; --light-gray: #f5f5f5; --dark-gray: #757575; --success: #4caf50; --warning: #ff9800; --info: #2196f3; }
    .tracking-container { background-color: white; border-radius: 8px; padding: 25px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
    .tracking-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid #eee; }
    .order-id { font-size: 18px; font-weight: 600; } .order-id span { color: var(--primary); }
    .tracking-progress { display: flex; justify-content: space-between; position: relative; margin: 40px 0; }
    .progress-line { position: absolute; height: 3px; background-color: #e0e0e0; top: 15px; left: 0; right: 0; z-index: 1; }
    .progress-line-active { background-color: var(--primary); width: 0%; transition: width 0.5s ease; }
    .status-step { display: flex; flex-direction: column; align-items: center; z-index: 2; flex: 1; }
    .status-icon { width: 32px; height: 32px; border-radius: 50%; background-color: #e0e0e0; display: flex; justify-content: center; align-items: center; margin-bottom: 10px; color: white; transition: all 0.3s ease; }
    .status-icon.active { background-color: var(--primary); transform: scale(1.1); }
    .status-icon.completed { background-color: var(--success); } .status-icon.delayed { background-color: var(--warning); }
    .status-label { font-size: 14px; text-align: center; color: var(--dark-gray); transition: all 0.3s ease; }
    .status-label.active { color: var(--primary); font-weight: 600; }
    .status-date { font-size: 12px; color: var(--dark-gray); margin-top: 5px; transition: all 0.3s ease; }
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
    .status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; margin-left: 10px; }
    .status-badge.filed { background-color: #e3f2fd; color: var(--info); } .status-badge.sent { background-color: #fff3e0; color: var(--warning); }
    .status-badge.processing { background-color: #e8f5e9; color: var(--success); } .status-badge.completed { background-color: #e8f5e9; color: var(--success); } .status-badge.delayed { background-color: #ffebee; color: #f44336; }
    .timeline-container { position: relative; margin: 30px 0; }
    .timeline { position: relative; max-width: 100%; margin: 0 auto; } .timeline::after { content: ''; position: absolute; width: 3px; background-color: #e0e0e0; top: 0; bottom: 0; left: 50%; margin-left: -1.5px; }
    .timeline-item { padding: 10px 40px; position: relative; width: 50%; box-sizing: border-box; }
    .timeline-item::after { content: ''; position: absolute; width: 16px; height: 16px; background-color: white; border: 3px solid var(--primary); border-radius: 50%; top: 15px; z-index: 1; }
    .timeline-item.left { left: 0; } .timeline-item.right { left: 50%; } .timeline-item.left::after { right: -8px; } .timeline-item.right::after { left: -8px; }
    .timeline-content { padding: 15px; background-color: white; border-radius: 6px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
    .timeline-date { font-weight: 500; color: var(--primary); margin-bottom: 5px; } .timeline-desc { font-size: 14px; color: var(--dark-gray); }
    .progress-info { display: flex; justify-content: space-between; margin-top: 10px; font-size: 13px; color: var(--dark-gray); }
    .notification-toggle { display: flex; align-items: center; margin-top: 15px; } .toggle-label { margin-left: 10px; font-size: 14px; }
    .switch { position: relative; display: inline-block; width: 50px; height: 24px; } .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked+.slider { background-color: var(--primary); } input:checked+.slider:before { transform: translateX(26px); }
    .status-text { color: #3a3838ff; font-size: 0.875rem; font-weight: 500; }
    .status-value { font-size: 0.875rem; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 0.375rem; margin-left: 0.5rem; display: inline-block; transition: all 0.3s ease; }
    @media (max-width: 768px) {
        .document-details { grid-template-columns: repeat(2, 1fr); }
        .tracking-progress { flex-direction: column; align-items: flex-start; margin: 20px 0; }
        .status-step { flex-direction: row; margin-bottom: 15px; width: 100%; }
        .status-icon { margin-right: 15px; margin-bottom: 0; } .progress-line { display: none; }
        .action-buttons { flex-direction: column; gap: 10px; } .action-buttons .btn-group { width: 100%; justify-content: space-between; }
        .timeline::after { left: 31px; } .timeline-item { width: 100%; padding-left: 70px; padding-right: 25px; }
        .timeline-item.right { left: 0; } .timeline-item::after { left: 23px; } .timeline-item.right::after { left: 23px; }
    }
    @media (max-width: 480px) { .document-details { grid-template-columns: 1fr; } }
    @media print {
        body { visibility: hidden; background-color: white; }
        .container, .container * { visibility: visible; }
        .container { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; max-width: none !important; }
        .tracking-container { margin-top: 0 !important; padding-top: 0 !important; box-shadow: none !important; border: none !important; }
        .action-buttons, .notification-toggle, .alert-banner, .btn, .tracking-link, #history-link { display: none !important; }
        .status-badge, .status-icon, .timeline-item::after, .progress-line, .progress-line-active { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-size: 12pt; color: #000; } .timeline-item { page-break-inside: avoid; }
    }
</style>

@php
    $hasViewed = $document->logs && $document->logs->contains(fn($log) => stripos($log->action, 'viewed') !== false);
    $finalStates = ['completed', 'done', 'finished', 'approved', 'rejected'];
    $currentStatusLower = strtolower($document->status);
    $displayStatus = $document->status;

    if ($hasViewed && !in_array($currentStatusLower, $finalStates)) {
        $displayStatus = 'processing';
    } elseif (in_array($currentStatusLower, $finalStates)) {
        $displayStatus = 'completed';
    }
@endphp

<div class="container">
    <h1 class="text-2xl font-bold mb-4">Document Tracking</h1>
    <div class="tracking-container">
        <div class="tracking-header">
            <div class="order-id">Document Reference: <span id="document-number">{{ $document->document_number }}</span></div>
            <div class="status-badge filed" id="status-badge" hidden>{{ ucfirst($displayStatus) }}</div>
        </div>

        <div class="document-details">
            <div class="detail-card">
                <div class="detail-label">Date Created</div>
                <div class="detail-value" id="doc-created">{{ $document->created_at->format('M d, Y') }}</div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Document Type</div>
                <div class="detail-value" id="doc-type">{{ $document->documentType->name ?? ($document->type ?? 'RLM') }}</div>
            </div>
            <div class="detail-card" hidden>
                <div class="detail-label">Priority</div>
                <div class="detail-value" id="doc-priority">{{ $document->priority_level ?? ($document->priority ?? 'Normal') }}</div>
            </div>
            <div class="detail-card" hidden>
                <div class="detail-label">Expected Completion</div>
                <div class="detail-value" id="doc-expected">{{ $document->expected_completion_date ?? ($document->expected_completion ?? '-') }}</div>
            </div>
        </div>

        <div class="tracking-progress">
            <div class="progress-line"><div class="progress-line-active" id="progress-bar"></div></div>
            @foreach(['filed', 'sent', 'processing', 'completed'] as $step)
            <div class="status-step" id="step-{{ $step }}">
                <div class="status-icon">
                    @if($step == 'filed') <i class="fas fa-edit"></i>
                    @elseif($step == 'sent') <i class="fas fa-share"></i>
                    @elseif($step == 'processing') <i class="fas fa-hourglass-half"></i>
                    @else <i class="fas fa-check-circle"></i> @endif
                </div>
                <div class="status-label">{{ ucfirst($step) }}</div>
                <div class="status-date" id="date-{{ $step }}">
                    @php $log = $document->status_logs ? $document->status_logs->where('status', $step)->first() : null; @endphp
                    {{-- {{ $log ? $log->created_at->format('M d, h:i A') : '-' }} --}}
                </div>
            </div>
            @endforeach
        </div>

        <div class="progress-info" hidden>
            <div><span class="status-text">Current Status:</span><span class="status-value" id="current-status-text">{{ ucfirst($displayStatus) }}</span></div>
        </div>

        <div class="courier-info" hidden>
            <div>
                <div class="courier-name">Subject: <span id="document-subject">{{ $document->subject }}</span></div>
                <div class="tracking-number">Status: <span id="document-status">{{ ucfirst($displayStatus) }}</span></div>
                <div class="tracking-number">Assigned To: <span id="assigned-to">
                    @if ($document->current_office_id && $document->currentOffice) {{ $document->currentOffice->name }}
                    @elseif($document->office_id && $document->office) {{ $document->office->name }}
                    @elseif($document->assigned_to) {{ $document->assigned_to }}
                    @elseif($document->current_office) {{ $document->current_office }}
                    @else - @endif
                </span></div>
            </div>
            <a href="#" class="tracking-link" id="history-link" hidden>View History <i class="fas fa-external-link-alt"></i></a>
        </div>

        <div class="timeline-container" hidden>
            <h3>Document Timeline</h3>
            <div class="timeline" id="document-timeline"></div>
        </div>

        <div class="status-updates">
            <h3>Document Activity Logs</h3>
            <div id="activity-logs">
                @if ($document->logs)
                    @foreach ($document->logs->sortByDesc('created_at') as $log)
                        <div class="update-card">
                            <div class="update-icon"><i class="{{ $log->action == 'Document Sent' ? 'fas fa-share-square' : 'fas fa-info-circle' }}"></i></div>
                            <div>
                                <div>@if ($log->action == 'Document Sent') Document Sent @elseif(strpos($log->action, 'viewed') !== false) {{ $log->description }} @else {{ $log->description }} @endif</div>
                                <div class="update-time">{{ $log->created_at->format('F d, Y h:i A') }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="update-card"><div class="update-icon"><i class="fas fa-info-circle"></i></div><div><div>No activity logs available</div><div class="update-time">-</div></div></div>
                @endif
            </div>
        </div>

        <div class="notification-toggle" hidden>
            <label class="switch"><input type="checkbox" id="notification-toggle" checked><span class="slider"></span></label>
            <span class="toggle-label">Receive real-time status notifications</span>
        </div>

        <div class="action-buttons">
            <div class="btn-group">
                <button class="btn btn-outline" id="share-btn"><i class="fas fa-share-alt"></i> Share Tracking</button>
                <button class="btn btn-outline" id="print-btn"><i class="fas fa-print"></i> Print Summary</button>
            </div>
            <button class="btn btn-primary" id="contact-btn" hidden><i class="fas fa-envelope"></i> Contact Office</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initTrackingSystem();
        function initTrackingSystem() {
            const initialData = {
                id: document.getElementById('document-number').textContent,
                status: '{{ $displayStatus }}',
                timeline: @json($trackingData['timeline'] ?? []),
                activityLogs: @json($trackingData['activityLogs'] ?? [])
            };
            updateStatusIndicators(initialData.status);
            buildTimeline(initialData.timeline);
            addEventListeners();
            setInterval(() => { Livewire.dispatch('refreshTracking'); }, 15000);
            Livewire.on('trackingUpdated', (data) => updateTrackingUI(data));
            document.addEventListener('visibilitychange', function() { if (!document.hidden) Livewire.dispatch('refreshTracking'); });
        }

        function updateStatusIndicators(status) {
            const normalizedStatus = normalizeStatus(status);
            const steps = ['filed', 'sent', 'processing', 'completed'];
            const statusIndex = steps.indexOf(normalizedStatus);
            const statusBadge = document.getElementById('status-badge');
            
            statusBadge.className = 'status-badge ' + normalizedStatus;
            statusBadge.textContent = getStatusDisplayText(status);
            
            const currentStatusText = document.getElementById('current-status-text');
            currentStatusText.textContent = getStatusDisplayText(status);
            currentStatusText.style.color = getStatusColor(status);
            currentStatusText.style.backgroundColor = getStatusBgColor(status);
            document.getElementById('document-status').textContent = getStatusDisplayText(status);

            if (statusIndex === -1) { updateStatusIndicators('processing'); return; }

            steps.forEach(step => {
                const stepElement = document.getElementById(`step-${step}`);
                stepElement.querySelector('.status-icon').classList.remove('completed', 'active', 'delayed');
                stepElement.querySelector('.status-label').classList.remove('active');
                stepElement.querySelector('.status-date').classList.remove('active');
            });

            for (let i = 0; i <= statusIndex; i++) {
                const stepElement = document.getElementById(`step-${steps[i]}`);
                const icon = stepElement.querySelector('.status-icon');
                if (i < statusIndex) icon.classList.add('completed');
                else {
                    icon.classList.add('active');
                    stepElement.querySelector('.status-label').classList.add('active');
                    stepElement.querySelector('.status-date').classList.add('active');
                }
            }
            document.getElementById('progress-bar').style.width = `${(statusIndex / (steps.length - 1)) * 100}%`;
        }

        function normalizeStatus(status) {
            const map = { 'filed': 'filed', 'sent': 'sent', 'processing': 'processing', 'in_progress': 'processing', 'viewed': 'processing', 'completed': 'completed', 'done': 'completed', 'finished': 'completed', 'approved': 'completed', 'rejected': 'completed' };
            return map[status.toLowerCase()] || 'processing';
        }

        function getStatusDisplayText(status) {
            const map = { 'filed': 'Filed', 'sent': 'Sent', 'processing': 'Processing', 'in_progress': 'In Progress', 'approved': 'Approved', 'rejected': 'Rejected', 'completed': 'Completed' };
            return map[status.toLowerCase()] || status;
        }

        function getStatusColor(s) {
            switch (s.toLowerCase()) { case 'filed': return '#2196f3'; case 'sent': return '#ff9800'; case 'processing': return '#4caf50'; case 'approved': case 'completed': return '#0e743c'; case 'rejected': return '#dc3545'; default: return '#6B7280'; }
        }

        function getStatusBgColor(s) {
            switch (s.toLowerCase()) { case 'filed': return '#e3f2fd'; case 'sent': return '#fff3e0'; case 'processing': return '#e8f5e9'; case 'approved': case 'completed': return '#d1e7dd'; case 'rejected': return '#f8d7da'; default: return '#F3F4F6'; }
        }

        function buildTimeline(data) {
            const container = document.getElementById('document-timeline');
            container.innerHTML = '';
            data.forEach((item, index) => {
                const el = document.createElement('div');
                el.className = `timeline-item ${index % 2 === 0 ? 'left' : 'right'}`;
                el.innerHTML = `<div class="timeline-content"><div class="timeline-date">${item.date}</div><div class="timeline-title">${item.title}</div><div class="timeline-desc">${item.description}</div></div>`;
                container.appendChild(el);
            });
        }

        function updateTrackingUI(data) {
            let currentStatus = data.status;
            let currentLogs = data.activityLogs || [];
            const hasViewedLog = currentLogs.some(log => (log.description && log.description.toLowerCase().includes('viewed')) || (log.action && log.action.toLowerCase().includes('viewed')));
            const completedStates = ['completed', 'done', 'finished', 'approved', 'rejected'];
            
            if (hasViewedLog && !completedStates.includes(currentStatus.toLowerCase())) currentStatus = 'processing';
            if (currentStatus) {
                updateStatusIndicators(currentStatus);
                const prev = document.getElementById('current-status-text').textContent.trim().toLowerCase();
                if (document.getElementById('notification-toggle').checked && prev !== currentStatus.toLowerCase()) showStatusChangeNotification(currentStatus);
            }
            if (data.assignedTo) document.getElementById('assigned-to').textContent = data.assignedTo;
            if (data.statusDates) Object.keys(data.statusDates).forEach(s => { if (data.statusDates[s] && data.statusDates[s] !== '-') document.getElementById(`date-${s}`).textContent = data.statusDates[s]; });
            if (data.timeline && data.timeline.length > 0) buildTimeline(data.timeline);
            if (data.activityLogs && data.activityLogs.length > 0) updateActivityLogs(data.activityLogs);
        }

        function updateActivityLogs(newLogs) {
            const container = document.getElementById('activity-logs');
            newLogs.forEach(log => {
                let exists = false;
                container.querySelectorAll('.update-card').forEach(ex => { if (ex.querySelector('.update-time').textContent === log.created_at) exists = true; });
                if (!exists) {
                    const el = document.createElement('div'); el.className = 'update-card';
                    el.innerHTML = `<div class="update-icon"><i class="${log.action === 'Document Sent' ? 'fas fa-share-square' : 'fas fa-info-circle'}"></i></div><div><div>${log.description}</div><div class="update-time">${log.created_at}</div></div>`;
                    container.insertBefore(el, container.firstChild);
                }
            });
        }

        function showStatusChangeNotification(newStatus) {
            const notif = document.createElement('div');
            notif.className = 'alert-banner'; notif.style.cssText = 'position:fixed;top:20px;right:20px;z-index:1000;max-width:300px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
            notif.innerHTML = `<i class="fas fa-bell"></i><span>Status updated: ${getStatusDisplayText(newStatus)}</span><button style="margin-left:auto;background:none;border:none;cursor:pointer;"><i class="fas fa-times"></i></button>`;
            document.body.appendChild(notif);
            notif.querySelector('button').addEventListener('click', () => document.body.removeChild(notif));
            setTimeout(() => { if (document.body.contains(notif)) document.body.removeChild(notif); }, 5000);
        }

        function addEventListeners() {
            document.getElementById('history-link').addEventListener('click', (e) => { e.preventDefault(); alert('Opening full history...'); });
            document.getElementById('share-btn').addEventListener('click', () => {
                const url = `${window.location.origin}/tracking/${document.getElementById('document-number').textContent}`;
                if (navigator.share) navigator.share({ title: 'Document Tracking', text: `Track document`, url: url });
                else navigator.clipboard.writeText(url).then(() => alert('Link copied!'));
            });
            document.getElementById('print-btn').addEventListener('click', () => window.print());
            document.getElementById('contact-btn').addEventListener('click', () => alert('Opening contact form...'));
            document.getElementById('notification-toggle').addEventListener('change', (e) => alert(e.target.checked ? 'Notifications enabled' : 'Notifications disabled'));
        }
    });
</script>