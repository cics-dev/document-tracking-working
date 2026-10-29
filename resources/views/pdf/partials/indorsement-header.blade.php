@php
    $ilEffectiveDate = $ilEffectiveDate ?? \Carbon\Carbon::parse($date_sent)->format('M d, Y');
    $ilRevisionCode = $ilRevisionCode ?? '00';
    $ilRevisionDate = $ilRevisionDate ?? 'N/A';
    $ilCode = $documentNumber ?? 'DRAFT';
    // Explicit line breaks keep long office prefixes inside the metadata cell in Dompdf.
    $ilCodeLines = explode('-', $ilCode, 2);
@endphp
<style>
    .il-header { width: 100%; border-collapse: collapse; table-layout: fixed; font-family: Arial, sans-serif; }
    .il-header td { border: 1px solid #222; padding: 4px 6px; vertical-align: middle; }
    .il-header .il-logo { width: 18%; text-align: center; }
    .il-header .il-logo img { width: 75px; height: auto; }
    .il-header .il-center { width: 43%; text-align: center; font-size: 11pt; font-weight: bold; line-height: 1.25; }
    .il-header .il-label { width: 19%; font-size: 8pt; font-weight: normal; line-height: 1.15; }
    .il-header .il-value { width: 20%; font-size: 8pt; font-weight: bold; line-height: 1.15; word-wrap: break-word; }
    .il-header .il-code { font-size: 8pt; }
    .il-heading { border: 1px solid #222; border-top: 0; padding: 12px 10px; text-align: center; }
    .il-heading .il-title { font-size: 12pt; font-weight: bold; }
    .il-heading .il-date { font-size: 10pt; margin-top: 3px; }
    .il-heading .il-subject { font-size: 11pt; font-weight: bold; margin-top: 12px; line-height: 1.25; }
</style>
<table class="il-header" cellspacing="0" cellpadding="0">
    <tr>
        <td class="il-logo" rowspan="6"><img src="{{ public_path('assets/img/zppsu-logo.png') }}" alt="University Logo"></td>
        <td class="il-center" rowspan="3">Zamboanga Peninsula Polytechnic State University</td>
        <td class="il-label">Unit</td><td class="il-value">{{ $unit ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="il-label">Document Code</td>
        <td class="il-value il-code">@foreach($ilCodeLines as $line){{ $line }}@if(! $loop->last)-<br>@endif@endforeach</td>
    </tr>
    <tr><td class="il-label">Date of Effectivity</td><td class="il-value">-</td></tr>
    <tr>
        <td class="il-center" rowspan="3">{{ $issuingOfficeName ?? 'N/A' }}</td>
        <td class="il-label">Revision Code</td><td class="il-value">{{ $ilRevisionCode }}</td>
    </tr>
    <tr><td class="il-label">Rev. Effectivity Date</td><td class="il-value">-</td></tr>
    <tr><td class="il-label">Page</td><td class="il-value">1 of {{ $ilPageCount ?? 1 }}</td></tr>
</table>
<div class="il-heading">
    <div class="il-title">INDORSEMENT</div>
    <div class="il-date">{{ \Carbon\Carbon::parse($date_sent)->format('F d, Y') }}</div>
    <div class="il-subject">SUBJECT: {{ strtoupper($subject) }}</div>
</div>
