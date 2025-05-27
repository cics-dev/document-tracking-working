<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Document Tracking</h1>
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-2">Document ID: {{ $document->document_number }}</h2>
        <p class="text-gray-700"><strong>Subject:</strong> {{ $document->subject }}</p>
        <p class="text-gray-700"><strong>Status:</strong> {{ $document->status }}</p>
    </div>

    <div class="mt-6 bg-white shadow-md rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Document Actions</h3>
        <ul class="space-y-4">
            @foreach($document->logs->sortByDesc('created_at') as $log)
                <li class="text-gray-700">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <span class="h-2 w-2 bg-gray-500 rounded-full mt-2"></span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">
                                @if($log->action == 'Document Sent')
                                    <strong>○ {{ $log->action }}</strong>
                                @elseif(strpos($log->action, 'viewed') !== false)
                                    <strong>○ {{ $log->description }}</strong>
                                @else
                                    <strong>○ {{ $log->description }}</strong>
                                @endif
                            </p>
                            <p class="text-sm text-gray-500">{{ $log->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>