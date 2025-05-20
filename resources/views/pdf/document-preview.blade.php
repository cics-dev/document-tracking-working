<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $documentNumber ?? 'Document Preview' }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            margin-top: 40px;
            margin-botton: 10px;
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
            margin-top: 50px;
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
    </style>
</head>
<body>
    <div class="header">
        <?php $image_path = '/assets/img/zppsu-logo.png'; ?>
        <img src="{{ public_path() . $image_path }}" alt="University Logo">
        <strong>Republic of the Philippines<br>
        ZAMBOANGA PENINSULA POLYTECHNIC STATE UNIVERSITY</strong><br>
        Region IX, Western Mindanao<br>
        R.T. Lim Boulevard, Baliwasan, Zamboanga City<br>
        Telephone No.: 955-4024 / 991-4012
    </div>

    <hr style="margin: 10px 0;">

    <div class="memo-info" style="margin: 0;">
        <p><strong>{{ strtoupper($documentType) }}</strong><br>
        {{ $documentNumber }}</p>
        <table>
            <tr>
                <td class="label">FOR</td>
                <td>: &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ strtoupper($toName) }}</strong><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $toPosition }}
                </td>
            </tr>
            <tr>
                <td class="label">FROM</td>
                <td>: &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ strtoupper($fromName) }}</strong><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $fromPosition }}
                </td>
            </tr>
            <tr>
                <td class="label">SUBJECT</td>
                <td>: &nbsp;&nbsp;&nbsp;&nbsp;<strong><u>{{ $subject }}</u></strong></td>
            </tr>
            <tr>
                <td class="label">DATE</td>
                <td>: &nbsp;&nbsp;&nbsp;&nbsp;{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</td>
            </tr>
        </table>
    </div>

    <hr>

    <div class="content" style="margin: 0;">
        {!! $content !!}
    </div>

    @if(!empty($signatories))
        <div class="signatory">
            @foreach(collect($signatories)->groupBy('role') as $role => $grouped)
                <div class="signatory-group">
                    @if (!empty($role))
                        <p class="signatory-label">{{ $role }}:</p>
                    @endif

                    <div class="signatory-row">
                        @foreach($grouped as $signatory)
                            <div class="signatory-box">
                                <strong>{{ $signatory['user_name'] }}</strong><br>
                                {{ $signatory['position'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

   @if (!empty($cfs) && count($cfs))
        <div class="cf-box">
            <span class="cf-label">CF:</span>
            <div class="cf-row">
                @foreach($cfs as $cf)
                    <span>{{ $cf['name'] }}</span><br>
                @endforeach
            </div>
        </div>
    @endif

</body>
</html>
