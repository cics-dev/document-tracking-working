<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 38px 40px 46px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; line-height: 1.45; color: #253044; }
        table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        .masthead { border-bottom: 3px solid #800000; margin-bottom: 17px; }
        .masthead td { vertical-align: top; padding-bottom: 13px; }
        .eyebrow { font-size: 8pt; color: #800000; letter-spacing: 1px; font-weight: bold; }
        h1 { font-size: 18pt; line-height: 1.2; margin: 6px 0 0; color: #202938; }
        .generated { text-align: right; font-size: 8pt; color: #647084; }
        .reference { font-size: 11pt; font-weight: bold; color: #800000; margin: 0 0 7px; word-wrap: break-word; }
        .subject { font-size: 10pt; font-weight: bold; margin-bottom: 13px; word-wrap: break-word; }
        .metadata { margin-bottom: 14px; }
        .metadata td { width: 50%; background: #f5f6f8; border: 3px solid white; padding: 9px 10px; vertical-align: top; word-wrap: break-word; }
        .label { font-size: 7pt; letter-spacing: .6px; color: #647084; text-transform: uppercase; margin-bottom: 3px; }
        .status { color: #800000; font-weight: bold; }
        h2 { font-size: 10pt; margin: 18px 0 7px; color: #253044; page-break-after: avoid; }
        .data thead { display: table-header-group; }
        .data th { background: #edf0f4; text-align: left; font-size: 8pt; color: #445066; padding: 8px; border-bottom: 1px solid #cbd2dc; }
        .data td { padding: 6px; vertical-align: top; border-bottom: 1px solid #e2e6eb; word-wrap: break-word; }
        .data tr { page-break-inside: avoid; }
        .muted { color: #647084; font-size: 8pt; }
        .note { margin-top: 16px; padding-top: 9px; border-top: 1px solid #d5dae2; color: #647084; font-size: 7pt; }
    </style>
</head>
<body>
    <table class="masthead"><tr>
        <td style="width:72%"><div class="eyebrow">ZPPSU · DOCUMENT TRACKING</div><h1>Tracking Summary</h1></td>
        <td class="generated">INTERNAL COPY<br>{{ $printedAt->format('M d, Y') }}<br>{{ $printedAt->format('h:i A') }} PHT</td>
    </tr></table>
    <div class="reference">{{ $document->document_number }}</div>
    <div class="subject">{{ $document->subject }}</div>
    <table class="metadata">
        <tr><td><div class="label">Document type</div>{{ $document->documentType?->name ?? '—' }}</td><td><div class="label">Current status</div><span class="status">{{ $document->status }}</span></td></tr>
        <tr><td><div class="label">From office</div>{{ $document->fromOffice?->name ?? '—' }}</td><td><div class="label">To</div>{{ $document->toOffice?->name ?? $document->to_text ?? '—' }}</td></tr>
        <tr><td><div class="label">Created</div>{{ $document->created_at->timezone('Asia/Manila')->format('M d, Y · h:i A') }} PHT</td><td><div class="label">Summary generated</div>{{ $printedAt->format('M d, Y · h:i A') }} PHT</td></tr>
    </table>
    <h2>Workflow</h2>
    <table class="data">
        <thead><tr><th style="width:29%">Step</th><th style="width:34%">Office</th><th style="width:14%">Status</th><th style="width:23%">Completed</th></tr></thead>
        <tbody>
            @forelse($steps as $step)
                <tr><td>{{ $step->step_label }}</td><td>{{ $step->office?->name ?? 'Unassigned' }}</td><td>{{ $step->status }}</td>
                    <td>@if($step->processed_at){{ $step->processed_at->timezone('Asia/Manila')->format('M d, Y') }}<br><span class="muted">{{ $step->processed_at->timezone('Asia/Manila')->format('h:i A') }} PHT</span>@else<span class="muted">—</span>@endif</td></tr>
            @empty
                <tr><td colspan="4" class="muted">No workflow steps.</td></tr>
            @endforelse
        </tbody>
    </table>
    <h2>Recent Activity <span class="muted">· Latest 10 entries</span></h2>
    <table class="data">
        <thead><tr><th style="width:24%">Date / Time (PHT)</th><th style="width:76%">Activity</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
                <tr><td>{{ $log->created_at->timezone('Asia/Manila')->format('M d, Y') }}<br><span class="muted">{{ $log->created_at->timezone('Asia/Manila')->format('h:i A') }}</span></td><td>{{ $log->description ?: ucfirst($log->action) }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">No activity recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="note">Internal university tracking summary. This is a snapshot, not the original document or a signed approval. All times are Philippine time.</p>
</body>
</html>
