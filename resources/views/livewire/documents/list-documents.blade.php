<section class="w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ ucfirst($mode) . ' Documents' }}</h2>
        @if($mode == 'sent')
            <a
                href="{{ route('documents.create-document') }}"
                class="text-sm bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition"
            >
                + Create Document
            </a>
        @endif
    </div>
    
    @if($mode == 'sent')
    <div class="flex space-x-2 mb-4">
        <button
            wire:click="switchDocumentTypeTab('inter')"
            @class([
                'px-3 py-1 rounded-md text-sm',
                $documentTypeTab === 'inter'
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
            ])
        >
            Inter-office Documents
        </button>
        <button
            wire:click="switchDocumentTypeTab('intra')"
            @class([
                'px-3 py-1 rounded-md text-sm',
                $documentTypeTab === 'intra'
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
            ])
        >
            Intra-office Documents
        </button>
    </div>
    @endif

    <div class="overflow-x-auto rounded-lg shadow-sm bg-white dark:bg-gray-800">
        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
            <thead class="text-xs text-gray-500 uppercase border-b bg-gray-100 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center" style="width:20%">Document Number</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center" style="width:28%">Subject</th>
                    @if($documentTypeTab != 'intra')
                        <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center">{{ $mode == 'sent' ? 'To' : 'From' }}</th>
                    @endif
                    @if($mode == 'all' && $documentTypeTab != 'intra')
                        <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center">To</th>
                    @endif
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center">Doc Type</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center" style="width:10%">Status</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center">Date Sent</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents->sortByDesc('updated_at') as $index => $document)
                    <tr class="border-b dark:border-gray-600 {{ $document->viewed_at || $mode == 'sent' || $office_name == 'Administration' || $office_name == 'Records Section' ? 'font-normal' : 'font-bold' }}">
                        <td class="px-4 py-2 items-center gap-2 border-r border-gray-300 dark:border-gray-600">
                            @if(is_null($document->viewed_at) && $mode != 'sent' && $office_name != 'Administration' && $office_name != 'Records Section')
                                <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                            @endif
                            {{ $document->document_number??'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $document->subject }}</td>
                        @if($documentTypeTab != 'intra')
                            <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">
                                {{ $mode == 'sent' ? ($document->toOffice->name ?? $document->to_text ?? 'N/A') : ($document->fromOffice->name ?? 'N/A') }}
                            </td>
                        @endif
                        @if($mode == 'all' && $documentTypeTab != 'intra')
                            <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $document->toOffice->name ?? $document->to_text ?? 'N/A' }}</td>
                        @endif
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $document->documentType->abbreviation ?? 'N/A' }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center align-middle uppercase 
                            @if(strtolower($document->status) == 'approved') text-green-600 dark:text-green-400
                            @elseif(strtolower($document->status) == 'rejected') text-red-600 dark:text-red-400
                            @elseif(strtolower($document->status) == 'sent') text-blue-600 dark:text-blue-400
                            @else text-gray-800 dark:text-gray-200 @endif">
                            {{ $document->status ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $document->date_sent }}</td>
                        <td class="px-4 py-2">
                            <div class="flex justify-center space-x-2">
                                @if($mode == 'received' || $documentTypeTab == 'intra')
                                    <button 
                                        wire:click="viewDocument('{{ $document['document_number'] }}')" 
                                        class="px-3 py-1 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white transition-colors"
                                    >
                                        View
                                    </button>
                                @elseif($mode == 'sent' && $document->status != 'draft'&& $document->status != 'Rejected')
                                    <button 
                                        wire:click="trackDocument('{{ $document['document_number'] }}')" 
                                        class="px-3 py-1 text-sm rounded-md bg-yellow-500 hover:bg-yellow-600 text-white transition-colors"
                                    >
                                        Track
                                    </button>
                                    <button 
                                        wire:click="viewDocument('{{ $document['document_number'] }}')" 
                                        class="px-3 py-1 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white transition-colors"
                                    >
                                        View
                                    </button>
                                @elseif($mode == 'sent' && $document->status == 'Rejected')
                                    <button 
                                        wire:click="trackDocument('{{ $document['document_number'] }}')" 
                                        class="px-3 py-1 text-sm rounded-md bg-yellow-500 hover:bg-yellow-600 text-white transition-colors"
                                    >
                                        Track
                                    </button>
                                    <a 
                                        href="{{ route('documents.create-revision', $document->document_number) }}"
                                        class="px-3 py-1 text-sm rounded-md bg-blue-500 hover:bg-gray-600 text-white transition-colors"
                                    >
                                        Revise
                                    </a>
                                    <button 
                                        wire:click="viewDocument('{{ $document['document_number'] }}')" 
                                        class="px-3 py-1 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white transition-colors"
                                    >
                                        View
                                    </button>
                                @else
                                    @if($document->status != 'draft')
                                        <button 
                                            wire:click="viewDocument('{{ $document['document_number'] }}')" 
                                            class="px-3 py-1 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white transition-colors"
                                        >
                                            View
                                        </button>
                                        <button 
                                            wire:click="trackDocument('{{ $document['document_number'] }}')" 
                                            class="px-3 py-1 text-sm rounded-md bg-yellow-500 hover:bg-yellow-600 text-white transition-colors"
                                        >
                                            Track
                                        </button>
                                    @endif
                                @endif
                                @if($document->status == 'draft')
                                    <button 
                                        wire:click="editDocument({{ $document['id'] }})" 
                                        class="px-3 py-1 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                                    >
                                        Edit
                                    </button>
                                    {{-- <button 
                                        wire:click="deleteDocument('{{ $document['id'] }}')" 
                                        class="px-3 py-1 text-sm rounded-md bg-red-600 hover:bg-red-700 text-white transition-colors"
                                    >
                                        Delete
                                    </button> --}}
                                @endif
                            </div>
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