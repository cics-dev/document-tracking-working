<x-mail::message>
# Hello, {{ $recipientName }}

Your document has been **{{ $action }}**.

<x-mail::panel>
**Subject:** {{ $document->subject }}  
**Document No:** {{ $document->document_number }}  
**From Office:** {{ $document->fromOffice->name ?? 'Unknown' }}  
**Current Status:** {{ $document->status }}  
**Date Updated:** {{ now()->format('F d, Y h:i A') }}
</x-mail::panel>

@if($remarks)
<x-mail::panel>
**Remarks:** {{ $remarks }}
</x-mail::panel>
@endif

Please log in to view full details.

Thanks,<br>
Document Tracking System Team
</x-mail::message>