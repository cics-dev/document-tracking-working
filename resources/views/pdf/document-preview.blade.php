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
                $code = 'SAO(ADMIN)-SO-4-2025';

                $parts = explode('-', $code); // ['SAO(ADMIN)', 'SO', '4', '2025']

                if (count($parts) === 4) {
                    $number = $parts[2];
                    $year = $parts[3];
                    $formatted = 'No. ' . $number . ', s. ' . $year;

                    echo $formatted; // Output: No. 4, s. 2025
                }
            ?>
        @endif
        <table>
            @if(!empty($toName) && $documentType != 'Special Order')
                <tr>
                    <td class="label">{{ $documentType == 'Intra' || $documentType == 'IOM'?'TO':'FOR' }}</td>
                    <td>: &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ strtoupper($toName) }}</strong><br>
                        @if($toPosition != 'N/A' && $toPosition != 'NA' && $toPosition != '')
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $toPosition }}
                        @endif
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
                    <td class="label" style="padding-top:25px;">FROM</td>
                    <td>
                        <img 
                            src="{{ public_path('storage/assets/img/fakesig1.png') }}" 
                            alt="Signature" 
                            style="height: 30px; padding-left: 20px"
                        ><br>
                        : &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ strtoupper($fromName) }}</strong><br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $fromPosition }}
                    </td>
                </tr>
            @endif
            <tr>
                <td class="label">SUBJECT</td>
                <td>: &nbsp;&nbsp;&nbsp;&nbsp;<strong><u>{{ $subject }}</u></strong></td>
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

    <div class="content" style="line-height: {{ $documentType == 'External Communication Response Letter' ? '0.25' : '1.5' }};">
        {!! $content !!}
    </div>

    @if(!empty($signatories))
        <div class="signatory">
            @foreach(collect($signatories)->groupBy('role') as $role => $grouped)
                <div class="signatory-group">
                    @if (!empty($role) && $documentType == 'Request Letter Memorandum')
                        <p class="signatory-label">{{ $role }}:</p>
                    @endif

                    <div class="signatory-row">
                        @foreach($grouped as $signatory)
                            <div class="signatory-box">
                                @if(isset($signatory['signature']) && $signatory['signature'] && $signatory['signed'])
                                    <img 
                                        src="{{ public_path('storage/' . $signatory['signature']) }}" 
                                        alt="Signature" 
                                        style="height: 50px; margin-bottom: 10px;"
                                    ><br>
                                @endif
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

    <footer style="position: fixed; bottom: 30px; width: 100%; text-align: left; font-size: 12px;">
        @if ($documentType === 'Special Order' || $documentType === 'External Communication Response Letter')
            {{ $documentNumber }}
        @endif
    </footer>

    {{-- <div class="page-break"></div>  <!-- This forces a page break -->

    <div style="text-align: center; margin-top: 50px;">
        <h3>ATTACHMENT</h3>
        
        @if(isset($attachment) && $attachment) --}}
            <!-- Option 1: Display as embedded PDF (if the PDF renderer supports it) -->
            {{-- <embed 
                src="{{ public_path('storage/' . $attachment) }}" 
                type="application/pdf" 
                width="80%" 
                height="600px"
                style="border: 1px solid #ccc; margin-top: 20px;"
            > --}}
            
            <!-- Option 2: Display as image (if you've converted the first page to an image) -->
            {{-- <img 
                src="{{ public_path('storage/' . $attachment) }}" 
                alt="PDF Attachment Preview" 
                style="max-width: 80%; border: 1px solid #ccc; margin-top: 20px;"
            >
            
            <p style="margin-top: 10px;">
                <strong>Attachment:</strong> {{ basename($attachment) }}
            </p>
        @else
            <p>No attachment available</p>
        @endif
    </div> --}}

    {{-- @php
        use Spatie\PdfToImage\Pdf;

        $pdfPath = storage_path('app/public/' . $attachment);
        $pdf = new Pdf($pdfPath);

        $pages = $pdf->getNumberOfPages();
        $imagePaths = [];

        for ($i = 1; $i <= $pages; $i++) {
            $imagePath = storage_path("app/public/pdf_images/attachment_page_$i.jpg");
            $pdf->setPage($i)->saveImage($imagePath);
            $imagePaths[] = 'storage/pdf_images/attachment_page_' . $i . '.jpg';
        }
        dd($imagePaths);
    @endphp

    @if ($imagePaths)
        <div class="page-break"></div>
        <h3>Attachment</h3>
        <img src="{{ public_path('storage/' . $imagePaths) }}" style="max-width: 100%; height: auto;" alt="Attachment">
    @endif --}}
</body>
</html>
