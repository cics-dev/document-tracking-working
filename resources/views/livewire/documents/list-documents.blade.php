<section class="w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ ucfirst($mode) . ' Documents' }}</h2>
        @if($mode == 'sent')
            <a
                href="{{route('documents.create-document')}}"
                class="text-sm bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition"
            >
                + Create Document
            </a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
            <thead class="text-xs text-gray-500 uppercase border-b dark:text-gray-400 dark:border-gray-600">
                <tr>
                    {{-- @if($mode == 'received') <th class="px-4 py-2">Access Code</th>@endif --}}
                    <th class="px-4 py-2">Document Number</th>
                    <th class="px-4 py-2">Subject</th>
                    <th class="px-4 py-2">{{ $mode == 'sent'?'To':'From' }}</th>
                    @if($mode == 'all')<th class="px-4 py-2">To</th>@endif
                    <th class="px-4 py-2">Dcoument Type</th>
                    <th class="px-4 py-2">Date Sent</th>
                    {{-- @if($mode == 'sent') <th class="px-4 py-2">Status</th> @endif --}}
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents->sortByDesc('created_at') as $index => $document)
                    <tr class="border-b dark:border-gray-600 {{ $document->viewed_at || $mode == 'sent' || $office_name == 'Administration' || $office_name == 'Records Section' ? 'font-normal' : 'font-bold' }}">
                        <td class="px-4 py-2 flex items-center gap-2">
                            @if(is_null($document->viewed_at) && $mode != 'sent' && $office_name != 'Administration' && $office_name != 'Records Section')
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                            @endif
                            {{-- @if($mode == 'received') {{ auth()->user()->office->abbreviation.$document->fromOffice->abbreviation.'-MEMO-DOC-2025-#'.$index + 1 }}
                            @else {{ $document->document_number }}
                            @endif --}}
                            {{ $document->document_number }}
                        </td>
                        @if($mode == 'received')<td class="px-4 py-2">{{ $document->document_number }}</td>@endif
                        <td class="px-4 py-2">{{ $document->subject }}</td>
                        <td class="px-4 py-2">{{ $mode == 'sent' ? $document->toOffice->name : $document->fromOffice->name }}</td>
                        @if($mode == 'all')<td class="px-4 py-2">{{ $document->toOffice->name }}</td>@endif
                        <td class="px-4 py-2">{{ $document->documentType->name }}</td>
                        <td class="px-4 py-2">{{ $document->date_sent }}</td>
                        {{-- <td class="px-4 py-2">{{ $document->status }}</td> --}}
                        <td class="px-4 py-2 space-x-2">
                            @if($mode == 'received')
                                <button wire:click="viewDocument('{{ $document['document_number'] }}')" class="text-[#3366FF] dark:text-[#99BBFF] hover:underline">View</button>
                            @elseif($mode == 'sent')
                                <button wire:click="trackDocument('{{ $document['document_number'] }}')" class="text-[#3366FF] dark:text-[#99BBFF] hover:underline">Track</button>
                            @else
                                <button wire:click="viewDocument('{{ $document['document_number'] }}')" class="text-[#3366FF] dark:text-[#99BBFF] hover:underline">View</button>
                                <button wire:click="trackDocument('{{ $document['document_number'] }}')" class="text-[#3366FF] dark:text-[#99BBFF] hover:underline">Track</button>
                            @endif
                            @if($document->status == 'draft')
                                <button wire:click="editDocument({{ $document['id'] }})" class="text-[#3366FF] dark:text-[#99BBFF] hover:underline">Edit</button>
                                <button wire:click="deleteDocument('{{ $document['id'] }}')" class="text-[#f53003] dark:text-[#FF4433] hover:underline">Delete</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No documents found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>