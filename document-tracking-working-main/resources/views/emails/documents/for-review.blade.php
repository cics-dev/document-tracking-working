<x-mail::message>
# Hello, {{ $recipientName }}

A new document has been forwarded to your office and requires your attention.

<x-mail::panel>
**Subject:** {{ $document->subject }}  
**From:** {{ $document->fromOffice->name ?? 'Unknown' }}  
**Document No:** {{ $document->document_number }}  
**Date:** {{ $document->created_at->format('F d, Y h:i A') }}
</x-mail::panel>

Please log in to the system to review, sign, or route this document.

{{-- <x-mail::button :url="route('documents.show', $document->id)">
View Document
</x-mail::button> --}}

Thanks,<br>
Document Tracking System Team
</x-mail::message>