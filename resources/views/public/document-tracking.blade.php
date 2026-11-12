<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive"><meta name="referrer" content="no-referrer">
    <title>Public Document Tracking</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f5f6f8;color:#252525;font:15px/1.5 system-ui,sans-serif}
        main{max-width:1200px;margin:40px auto;padding:28px;background:white;border:1px solid #e5e7eb;border-radius:12px}
        header{display:flex;align-items:center;justify-content:space-between;gap:16px;border-bottom:1px solid #eee;padding-bottom:20px}
        h1{font-size:20px;margin:0;overflow-wrap:anywhere}h1 span{color:#800000}.badge{background:#fff3e0;color:#9a4b00;border-radius:20px;padding:5px 12px;font-size:13px;font-weight:600}
        .details{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin:24px 0}.card{padding:18px;background:#f5f5f5;border-radius:8px}.label{color:#666;font-size:13px;margin-bottom:7px}.value{font-weight:600}
        .timeline{display:flex;overflow-x:auto;gap:12px;padding:28px 0;list-style:none;margin:0}.step{flex:1;min-width:145px;text-align:center;border-top:3px solid #ddd;padding:18px 8px 0}.step strong,.step span{display:block}.step span{font-size:13px;color:#666;margin-top:6px}.step.completed{border-color:#32925b}.step.active{border-color:#800000}.step.rejected{border-color:#ba2929}.step.active strong{color:#800000}.step.rejected strong{color:#ba2929}
        footer{font-size:12px;color:#777;border-top:1px solid #eee;padding-top:18px;margin-top:10px}
        @media(max-width:700px){main{margin:12px;padding:18px}header{align-items:flex-start}.details{grid-template-columns:repeat(2,minmax(0,1fr))}.timeline{display:block}.step{text-align:left;border-top:0;border-left:3px solid #ddd;padding:4px 16px 18px;margin-left:8px}}
    </style>
</head>
<body>
<main>
    <header><h1>Document Reference: <span>{{ $tracking['reference'] }}</span></h1><span class="badge">{{ $tracking['status'] }}</span></header>
    <div class="details">
        <div class="card"><div class="label">Date Created</div><div class="value">{{ $tracking['created'] }}</div></div>
        <div class="card"><div class="label">Document Type</div><div class="value">{{ $tracking['type'] }}</div></div>
        <div class="card"><div class="label">Current Status</div><div class="value">{{ $tracking['status'] }}</div></div>
        <div class="card"><div class="label">Overall Progress</div><div class="value">{{ $tracking['progress'] }}% Complete</div></div>
    </div>
    @php
        $waiting = true;
        $stopped = false;
    @endphp
    <ol class="timeline" aria-label="Workflow progress">
        @foreach($tracking['steps'] as $step)
            @php
                $rejected = in_array($step['status'], ['Rejected', 'Returned'], true);
                $completed = $step['completedAt'] !== null;
                $active = $waiting && ! $completed && ! $stopped && ! str_starts_with($tracking['status'], 'Generated ') && in_array($tracking['status'], ['Sent', 'In Process', 'Approved'], true);
                if ($rejected) $stopped = true;
                if ($active || $rejected) $waiting = false;
            @endphp
            <li class="step {{ $rejected ? 'rejected' : ($completed ? 'completed' : ($active ? 'active' : '')) }}">
                <strong>{{ $step['office'] }}</strong><span>{{ $step['label'] }}</span>
                <span>{{ $step['status'] }}</span>
                @if($step['completedAt'])<span>{{ $step['completedAt'] }} PHT</span>@endif
            </li>
        @endforeach
    </ol>
    <footer>Public tracking view · Refresh this page for the latest status.</footer>
</main>
</body>
</html>
