<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $documentNumber ?? 'Document Preview' }}</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                margin-left: 40px;
            }

            .page {
                width: 816px;
                height: 1248px;
                margin: 0 auto;
                padding: 40px;
            }
        }
        .page-break { page-break-after: always; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            margin-top: 40px;
            margin-bottom: 10px;
        }
        span {
            font-size: 10px;
        }
        .signatory-group .cf-group {
            margin-bottom: 20px;
        }

        .signatory-label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .cf-label {
            font-weight: bold;
            width: 10px;
            margin-right: 50px;
        }

        .cf-row {
            width: 100%;
            margin-left: 40px;
        }

        .signatory-row {
            width: 100%;
        }

        .cf-box {
            display: inline-block;
            margin-right: 50px;
            margin-top: 50px;
            vertical-align: top;
            padding-top: 5px;
        }

        .signatory-box {
            display: inline-block;
            flex: 1 1 280px;
            max-width: 280px;
            margin-right: 50px;
            margin-top: 10px;
            vertical-align: top;
            padding-top: 5px;
        }

        .ql-align-center { text-align: center; }
        .ql-align-justify { text-align: justify; }
        .ql-align-right { text-align: right; }
        .ql-align-left { text-align: left; }

        ul {
            list-style-type: disc !important;
            padding-left: 20px !important;
            margin-left: 10px;
        }

        ol {
            list-style-type: decimal !important;
            padding-left: 20px !important;
            margin-left: 10px;
        }

        li {
            margin-bottom: 4px;
        }

        li::marker {
            content: "• ";
        }

        p {
            text-align: left;
        }
        .header {
            text-align: center;
            line-height: 1.5;
        }
        .header img {
            width: 80px;
            position: absolute;
            top: 40px;
            left: 40px;
        }
        .memo-info {
            margin-top: 30px;
        }
        .memo-info table {
            width: 100%;
            border-spacing: 0;
        }
        .memo-info td {
            padding: 5px 0;
        }
        .label {
            width: 100px;
            font-weight: bold;
            vertical-align: top;
        }
        hr {
            border: 1px solid black;
            margin: 20px 0;
        }
        .content {
            text-align: justify;
        }
        .signatory {
            margin-top: 40px;
        }

        .document-container {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.1;
        }

        .document-header {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            line-height: 1.1;
        }

        .document-header td {
            border: 1px solid black;
            padding: 0 5px;
            vertical-align: top;
        }

        .logo-area {
            vertical-align: middle !important;
            text-align: center;
        }

        .university-logo {
            max-width: 1.15in;
            height: auto;
            display: block;
            margin: 0 auto 2px;
        }

        .university-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            padding: 0 5px;
            margin: 0;
        }

        .office-title-area {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle !important;
            margin: 0;
        }

        .info-cell {
            font-weight: normal;
            text-align: left;
            padding-left: 10px !important;
            width: 100px;
        }

        .data-cell {
            font-weight: bold;
            text-align: left;
        }
        
        .info-cell, .data-cell {
            border-left: none;
        }
        
        .header-footer-section {
            border-left: 1px solid black;
            border-right: 1px solid black;
            border-bottom: 1px solid black;
            padding: 10px 5px;
            text-align: center;
            line-height: 1.1;
        }

        .indorsement-text {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        .subject-text {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    @php
        $status = null;
        if (isset($document)) {
            if (is_array($document)) {
                $status = $document['status'] ?? null;
            } elseif (is_object($document)) {
                $status = $document->status ?? null;
            }
        }
    @endphp
    @if ($documentType == 'Indorsement Letter')
        <div class="document-container">
            <table class="document-header" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="logo-area" rowspan="6" style="width: 26%;">
                        <div class="logo-wrapper">
                            <?php $image_path = '/assets/img/zppsu-logo.png'; ?>
                            <img src="{{ public_path() . $image_path }}" alt="University Logo" class="university-logo">
                        </div>
                    </td>
                    <td class="info-cell" rowspan="3" style="width: 50%;">
                        <div class="university-title">
                            Zamboanga Peninsula Polytechnic State University
                        </div>
                    </td>
                    <td class="info-cell" style="width: 12%;">Unit</td>
                    <td class="data-cell" style="width: 12%;">{{ $unit }}</td>
                </tr>
                <tr>
                    <td class="info-cell">Document Code</td>
                    <td class="data-cell">{{ $action == 'preview'?'':$documentNumber}}</td>
                </tr>
                <tr>
                    <td class="info-cell">Date of Effectivity</td>
                    <td class="data-cell"></td>
                </tr>
                <tr>
                    <td class="office-title-area" rowspan="3" style="width: 50%;">
                        Office of the Vice President for Administration and Finance
                    </td>

                    <td class="info-cell">Revision Code</td>
                    <td class="data-cell"></td>
                </tr>
                <tr>
                    <td class="info-cell">Rev. Effectivity Date</td>
                    <td class="data-cell"></td>
                </tr>
                <tr>
                    <td class="info-cell">Page</td>
                    <td class="data-cell">1 of 2</td>
                </tr>
            </table>
        </div>
        <div class="header-footer-section">
            <div class="indorsement-text">
                INDORSEMENT<br>
                {{ \Carbon\Carbon::parse($date_sent)->format('F d, Y') }}
            </div>
            <br>
            <div class="subject-text">
                SUBJECT: {{ strtoupper($subject) }}
            </div>
        </div>
    @else
        <div class="header">
            <?php $image_path = '/assets/img/zppsu-logo.png'; ?>
            <img src="{{ public_path() . $image_path }}" alt="University Logo">
            <strong>Republic of the Philippines<br>
            ZAMBOANGA PENINSULA POLYTECHNIC STATE UNIVERSITY</strong><br>
            Region IX, Western Mindanao<br>
            R.T. Lim Boulevard, Baliwasan, Zamboanga City<br>
            Telephone No.: 955-4024 / 991-4012
            @if(isset($office_logo) && $office_logo && $documentType=='Intra')
                <img src="{{ public_path('storage/' . $office_logo) }}" alt="Office Logo" style="left: 600px">
            @endif
        </div>

        <hr style="margin: 10px 0;">
        
        @if ($documentType == 'External Communication Response Letter')
        <br>{{ \Carbon\Carbon::parse($date_sent)->format('F d, Y') }}<br><br><br>
        @endif
        @if ($documentType != 'External Communication Response Letter')
        <div class="memo-info">
            @if($documentType == 'Intra')
                <p><strong>College Memorandum</strong><br>
            @else
                <p><strong>{{ strtoupper($documentType) }}</strong><br>
            @endif
            @if($documentType != 'Special Order')
                {{ $documentNumber }}</p>
            @else
                <?php
                    $parts = explode('-', $documentNumber ?? '');
                    if (count($parts) === 4) {
                        $number = $parts[2];
                        $year = $parts[3];
                        echo 'No. ' . $number . ', s. ' . $year;
                    } else {
                        echo $documentNumber;
                    }
                ?>
            @endif
            <table>
                @if(!empty($toName) && $documentType != 'Special Order' && $toPosition != 'N/A' && $toPosition != 'NA' && $toPosition != '')
                    <tr>
                        <td class="label">{{ $documentType == 'Intra' || $documentType == 'IOM'?'TO':'FOR' }}</td>
                        <td>: &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ strtoupper($toName) }}</strong><br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $toPosition }}
                        </td>
                    </tr>
                @endif
                @if(!empty($thru) && $documentType != 'Special Order')
                    <tr>
                        <td class="label">THRU</td>
                        <td>
                            : &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ strtoupper($thru) }}</strong><br>
                        </td>
                    </tr>
                @endif
                @if ($documentType != 'Special Order')
                    <tr>
                        <td class="label" style="{{ 
                            ($fromPosition == 'University President' && $status == 'Approved') || 
                            ($fromPosition != 'University President') 
                            ? 'padding-top:35px;' : '' }}">FROM</td>
                        <td>
                            @if(
                                ($fromPosition == 'University President' && $status == 'Approved') ||
                                ($fromPosition != 'University President')
                            )
                                @if(isset($document))
                                    @php
                                        $fromHeadSig = is_array($document) ? ($document['from_office']['head']['signature'] ?? null) : ($document->fromOffice->head->signature ?? null);
                                    @endphp
                                    <img 
                                        src="{{ public_path('storage/'.($fromHeadSig ?: 'assets/img/fakesig1.png')) }}" 
                                        alt="Signature" 
                                        style="height: 30px; padding-left: 20px"
                                    ><br>
                                @endif
                            @endif
                            : &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ strtoupper($fromName) }}</strong><br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $fromPosition }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td class="label">SUBJECT</td>
                    <td>: &nbsp;&nbsp;&nbsp;&nbsp;<strong><u>{{ strtoupper($subject) }}</u></strong></td>
                </tr>
                <br>
                <tr>
                    <td class="label">DATE</td>
                    <td>: &nbsp;&nbsp;&nbsp;&nbsp;{{ \Carbon\Carbon::parse($date_sent)->format('F d, Y') }}</td>
                </tr>
            </table>
        </div>
        <hr>
        @endif
    @endif

    <div class="content" style="line-height: {{ $documentType == 'External Communication Response Letter' ? '0.25' : '1.5' }};">
        {!! $content !!}
    </div>

    @if(!empty($signatories) && $documentType != 'Inter-Office Memorandum')
    <div class="signatory">
        @foreach(collect($signatories)->groupBy('role') as $role => $grouped)
                @if($role != "Approved by" 
                    || 
                    ($role == "Approved by" && $documentType != 'Request Letter Memorandum' && $documentType != 'Indorsement Letter'))
                    <div class="signatory-group">
                        @if (!empty($role) && $documentType == 'Request Letter Memorandum')
                            <p class="signatory-label">{{ $role }}:</p>
                        @endif

                        <div class="signatory-row">
                            @foreach($grouped as $signatory)
                                <div class="signatory-box">
                                    @if($signatory['signed'])
                                        @if(!empty($signatory['signed_for']))<span style="font-style: italic; vertical-align: top;">for</span>@endif
                                        @if(isset($signatory['signature']) && $signatory['signature'])
                                        <img 
                                            src="{{ public_path('storage/' . ($signatory['signature'] ?: 'assets/img/fakesig1.png')) }}"
                                            alt="Signature" 
                                            style="height: 50px; margin-bottom: 10px;"
                                        >
                                        @endif
                                        <br>
                                    @endif
                                    <strong>{{ strtoupper($signatory['user_name']) }}</strong><br>
                                    {{ $signatory['position'] }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($documentType === 'Request Letter Memorandum' || $documentType === 'Indorsement Letter')
                    <br><br>   
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 50%;"></td>
                            <td style="width: 50%; vertical-align: middle;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding-right: 1.5rem;">
                                            <p class="signatory-label">ACTION:</p>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 12px;"></td></tr>
                                    <tr>
                                        <td style="padding-right: 1.5rem;">
                                            <label style="font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                <input type="checkbox" name="status" value="Approved" style="transform: scale(2); vertical-align: middle; margin-right: 6px;"
                                                    {{ $status == 'Approved' ? 'checked' : '' }}>
                                                APPROVED
                                            </label>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 12px;"></td></tr>
                                    <tr>
                                        <td style="padding-right: 1.5rem;">
                                            <label style="font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                <input type="checkbox" name="checkbox1" value="Rejected" style="transform: scale(2); vertical-align: middle; margin-right: 6px;"
                                                    {{ $status == 'Rejected' ? 'checked' : '' }}>
                                                DISAPPROVED
                                            </label>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 12px;"></td></tr>
                                    <tr>
                                        <td style="padding-right: 1.5rem;">
                                            <label style="font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                <input type="checkbox" name="checkbox1" value="1" style="transform: scale(2); vertical-align: middle; margin-right: 6px;">
                                                OTHER COMMENT/S:
                                            </label>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 12px;"></td></tr>
                                    <tr>
                                        <td style="width: 40%">
                                            <div class="signatory-row" style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; width: 200px;">
                                                @foreach($grouped as $signatory)
                                                    <div class="signatory-box" style="text-align: center; width: 200px; padding-left: 40px;">
                                                        @if($signatory['signed'])
                                                            @if(!empty($signatory['signed_for']))<span style="font-style: italic; vertical-align: top;">for</span>@endif
                                                            @if(isset($signatory['signature']) && $signatory['signature'])
                                                            <img 
                                                                src="{{ public_path('storage/' . $signatory['signature']) }}" 
                                                                alt="Signature" 
                                                                style="height: 50px; margin-bottom: 10px;"
                                                            >
                                                            @endif
                                                            <br>
                                                        @endif
                                                        <strong>{{ strtoupper($signatory['user_name']) }}</strong><br>
                                                        {{ $signatory['position'] }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                @endif
            @endforeach
        </div>
    @endif

   @if (!empty($cfs) && count($cfs))
        <div class="cf-box">
            <span class="cf-label">CF:</span>
            <div class="cf-row">
                @foreach($cfs as $cf)
                    <span>{{ $cf['office'] }}</span><br>
                @endforeach
            </div>
        </div>
    @endif

    <footer style="position: fixed; bottom: 30px; width: 100%; text-align: left; font-size: 12px;">
        @if ($documentType === 'Special Order' || $documentType === 'External Communication Response Letter')
            {{ $documentNumber }}
        @endif
    </footer>
</body>
</html>
