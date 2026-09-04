<section class="w-full min-w-0 space-y-6">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ ucfirst($mode) . ' Documents' }}</flux:heading>
            <flux:subheading>Manage and track your office documents.</flux:subheading>
        </div>

        @if($mode == 'Sent')
            <flux:button href="{{ route('documents.create-document', $documentTypeTab === 'intra' ? ['level' => 'Intra'] : []) }}" variant="primary" icon="plus" class="w-full md:w-auto">
                {{ $documentTypeTab === 'intra' ? 'Create Intra Document' : 'Create Document' }}
            </flux:button>
        @endif
    </div>
    
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
        <div class="grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 [&>*]:min-w-0">
            <div class="w-full min-w-0">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search subject or number..." />
            </div>
            
            <div class="w-full min-w-0">
                <flux:select wire:model.live="statusFilter" placeholder="All statuses">
                    <flux:select.option value="Draft">Draft</flux:select.option>
                    <flux:select.option value="Sent">Sent</flux:select.option>
                    <flux:select.option value="In Process">In Process</flux:select.option>
                    <flux:select.option value="Approved">Approved</flux:select.option>
                    <flux:select.option value="Returned">Returned</flux:select.option>
                    <flux:select.option value="Rejected">Rejected</flux:select.option>
                </flux:select>
            </div>

            <div class="w-full min-w-0">
                <flux:input.group class="min-w-0 max-w-full">
                    <flux:input.group.prefix>From</flux:input.group.prefix>
                    <flux:input wire:model.live="dateFrom" type="date" aria-label="From date" />
                </flux:input.group>
            </div>
            <div class="w-full min-w-0">
                <flux:input.group class="min-w-0 max-w-full">
                    <flux:input.group.prefix>To</flux:input.group.prefix>
                    <flux:input wire:model.live="dateTo" type="date" aria-label="To date" />
                </flux:input.group>
            </div>

            @if($documentTypeTab == 'inter')
                <div class="w-full min-w-0">
                    <x-searchable-filter-select
                        model="typeFilter"
                        :options="$documentTypes->filter(fn ($type) => filled($type->abbreviation))->map(fn ($type) => ['value' => (string) $type->id, 'label' => $type->abbreviation])->values()->all()"
                        placeholder="All Types"
                        search-placeholder="Search document types..."
                    />
                </div>
            @endif

            <flux:button wire:click="resetFilters" variant="subtle" icon="arrow-path" class="w-full sm:w-auto">
                Reset filters
            </flux:button>
        </div>

        @if($mode == 'Sent')
        <div class="mt-3 flex w-full max-w-full flex-wrap rounded-md border border-gray-200 bg-white p-1 shadow-sm sm:w-fit">
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
                    <th class="px-6 py-3"><button wire:click="sort('document_number')">Document Number</button></th>
                    <th class="px-6 py-3 w-1/3"><button wire:click="sort('subject')">Subject</button></th>
                    
                    @if($documentTypeTab != 'intra')
                        <th class="px-6 py-3">{{ $mode == 'Sent' ? 'To' : 'From' }}</th>
                    @endif
                    
                    <th class="px-6 py-3 text-center">Type</th>
                    <th class="px-6 py-3 text-center"><button wire:click="sort('status')">Status</button></th>
                    <th class="px-6 py-3"><button wire:click="sort('created_at')">Date</button></th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($documents as $document)
                    <tr class="hover:bg-gray-50/50 transition-colors {{ $document->viewed_at || $mode == 'Sent' ? '' : 'bg-blue-50/30' }}">
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if(!$document->is_viewed_by_me && $mode != 'Sent')
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
                                {{ $mode == 'Sent' 
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
                                    'Approved' => 'green',
                                    'Rejected' => 'red',
                                    'Draft' => 'zinc',
                                    'Sent' => 'blue',
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
                                @if($document->status == 'Draft')
                                    <flux:button href="{{ route('documents.edit-draft', $document->id) }}" size="sm" icon="pencil-square" variant="subtle" class="text-blue-600">Edit</flux:button>
                                @endif

                                @if($document->isRevisableBy(auth()->user()))
                                    <flux:button href="{{ route('documents.create-revision', $document->document_number) }}" size="sm" icon="arrow-path" variant="filled" class="bg-blue-600 hover:bg-blue-700 text-white">Revise</flux:button>
                                @endif

                                @if($document->status != 'Draft')
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
