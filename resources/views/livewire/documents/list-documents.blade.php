<section class="w-full space-y-6">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ ucfirst($mode) . ' Documents' }}</flux:heading>
            <flux:subheading>Manage and track your office documents.</flux:subheading>
        </div>

        @if($mode == 'sent')
            <flux:button href="{{ route('documents.create-document') }}" variant="primary" icon="plus" class="w-full md:w-auto">
                Create Document
            </flux:button>
        @endif
    </div>
    
    <div class="flex flex-col md:flex-row gap-4 justify-between items-end md:items-center bg-gray-50 p-4 rounded-lg border border-gray-200">
        
        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto flex-1">
            <div class="w-full md:w-64">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search subject or number..." />
            </div>
            
            @if($mode == 'sent')
                <div class="w-full md:w-48">
                    <flux:select wire:model.live="statusFilter" placeholder="Filter Status">
                        <flux:select.option value="">All Statuses</flux:select.option>
                        <flux:select.option value="draft">Draft</flux:select.option>
                        <flux:select.option value="sent">Sent</flux:select.option>
                        <flux:select.option value="approved">Approved</flux:select.option>
                        <flux:select.option value="rejected">Rejected</flux:select.option>
                    </flux:select>
                </div>
            @endif

            @if($documentTypeTab == 'inter')
                <div class="w-full md:w-48">
                    <flux:select wire:model.live="typeFilter" placeholder="Filter Doc Type">
                        <flux:select.option value="">All Types</flux:select.option>
                        @foreach($documentTypes as $type)
                            @if (isset($type->abbreviation) && $type->abbreviation != '')
                                <flux:select.option value="{{ $type->id }}">{{ $type->abbreviation }}</flux:select.option>
                            @endif
                        @endforeach
                    </flux:select>
                </div>
            @endif
        </div>

        @if($mode == 'sent')
        <div class="flex bg-white rounded-md p-1 border border-gray-200 shadow-sm shrink-0">
            <button
                wire:click="switchDocumentTypeTab('inter')"
                @class([
                    'px-4 py-1.5 text-sm font-medium rounded transition-colors',
                    $documentTypeTab === 'inter' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'
                ])
            >
                Inter-office
            </button>
            <button
                wire:click="switchDocumentTypeTab('intra')"
                @class([
                    'px-4 py-1.5 text-sm font-medium rounded transition-colors',
                    $documentTypeTab === 'intra' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'
                ])
            >
                Intra-office
            </button>
        </div>
        @endif
    </div>

    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200 bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3">Document Number</th>
                    <th class="px-6 py-3 w-1/3">Subject</th>
                    
                    @if($documentTypeTab != 'intra')
                        <th class="px-6 py-3">{{ $mode == 'sent' ? 'To' : 'From' }}</th>
                    @endif
                    
                    <th class="px-6 py-3 text-center">Type</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($documents as $document)
                    <tr class="hover:bg-gray-50/50 transition-colors {{ $document->viewed_at || $mode == 'sent' ? '' : 'bg-blue-50/30' }}">
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if(!$document->is_viewed_by_me && $mode != 'sent')
                                    <span class="size-2 rounded-full bg-blue-600 animate-pulse"></span>
                                @endif
                                <span class="font-medium text-gray-900">{{ $document->document_number ?? '—' }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="line-clamp-2 text-gray-700" title="{{ $document->subject }}">
                                {{ $document->subject }}
                            </div>
                        </td>

                        @if($documentTypeTab != 'intra')
                            <td class="px-6 py-4 text-gray-600">
                                {{ $mode == 'sent' 
                                    ? ($document->toOffice->name ?? $document->to_text ?? '—') 
                                    : ($document->fromOffice->name ?? '—') }}
                            </td>
                        @endif

                        <td class="px-6 py-4 text-center">
                            <flux:badge size="sm" variant="outline" color="zinc">{{ $document->documentType->abbreviation ?? 'N/A' }}</flux:badge>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @php
                                $status = strtolower($document->status);
                                $color = match($status) {
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    'draft' => 'zinc',
                                    'sent' => 'blue',
                                    default => 'orange'
                                };
                            @endphp
                            <flux:badge size="sm" :color="$color" variant="solid" inset="top bottom">{{ ucfirst($status) }}</flux:badge>
                        </td>

                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                            {{ $document->created_at->format('M d, Y') }}<br>
                            <span class="text-xs">{{ $document->created_at->format('h:i A') }}</span>
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                @if($document->status == 'draft')
                                    <flux:button wire:click="editDocument({{ $document->id }})" size="sm" icon="pencil-square" variant="subtle" class="text-blue-600">Edit</flux:button>
                                @endif

                                @if($mode == 'sent' && $document->status == 'Rejected')
                                    <flux:button href="{{ route('documents.create-revision', $document->document_number) }}" size="sm" icon="arrow-path" variant="filled" class="bg-blue-600 hover:bg-blue-700 text-white">Revise</flux:button>
                                @endif

                                @if($mode == 'sent' && $document->status != 'draft')
                                    <flux:button wire:click="trackDocument('{{ $document->document_number }}')" size="sm" icon="map" variant="primary" class="px-3 py-1 text-sm rounded-md bg-yellow-500 hover:bg-yellow-600 text-white transition-colors">Track</flux:button>
                                @endif

                                <flux:button wire:click="viewDocument('{{ $document->document_number }}')" size="sm" icon="eye" variant="primary" class="px-3 py-1 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white transition-colors">View</flux:button>
                            </div>
                        </td>
                    </tr>@empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <flux:icon icon="document-magnifying-glass" class="size-10 mb-2 text-gray-300" />
                                <p class="text-base font-medium">No documents found</p>
                                <p class="text-sm">Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $documents->links() }}
    </div>
</section>