<section class="w-full min-w-0 space-y-6">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">External Documents</flux:heading>
            <flux:subheading>Manage documents received from outside agencies.</flux:subheading>
        </div>

        @if(auth()->user()->hasAccess('receive_external_documents'))
            <flux:button href="{{ route('documents.receive-external-document') }}" variant="primary" icon="plus" class="w-full md:w-auto">
                Receive Document
            </flux:button>
        @endif
    </div>

    <div class="grid min-w-0 grid-cols-1 gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 [&>*]:min-w-0">
        <div class="w-full min-w-0">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search subject or sender..." />
        </div>
        @if($showOfficeContext || auth()->user()->hasAccess('view_all_external_documents'))
        <div class="w-full min-w-0">
            <x-searchable-filter-select
                model="officeFilter"
                :options="$offices->map(fn ($office) => ['value' => (string) $office->id, 'label' => $office->name, 'search' => $office->abbreviation])->values()->all()"
                :placeholder="$allOfficesLabel"
                search-placeholder="Search offices..."
            />
        </div>
        @endif
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
        <flux:button wire:click="resetFilters" variant="subtle" icon="arrow-path" class="w-full sm:w-auto">
            Reset filters
        </flux:button>
    </div>

    <div class="min-w-0 overflow-x-auto rounded-xl shadow-sm border border-gray-200 bg-white">
        <table class="w-full text-sm text-left" style="table-layout: fixed; min-width: 46rem; overflow-wrap: anywhere;"><colgroup><col style="width: clamp(9rem, 17%, 11rem);"><col style="width: clamp(10rem, 22%, 14rem);"><col><col style="width: 8rem;"><col style="width: 6rem;"></colgroup>
            <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                <tr>
                    <x-sortable-column column="document_number" :sort-by="$sortBy" :sort-direction="$sortDirection" class="px-3 py-3">Document Number</x-sortable-column>
                    <x-sortable-column column="from" :sort-by="$sortBy" :sort-direction="$sortDirection" class="px-3 py-3">From</x-sortable-column>
                    <x-sortable-column column="subject" :sort-by="$sortBy" :sort-direction="$sortDirection" class="px-3 py-3">Subject</x-sortable-column>
                    <x-sortable-column column="received_date" :sort-by="$sortBy" :sort-direction="$sortDirection" class="px-3 py-3">Received Date</x-sortable-column>
                    <th class="px-3 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($documents as $document)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-2">
                                @if(!$document->is_viewed_by_me)
                                    <span class="size-2 rounded-full bg-blue-600 animate-pulse"></span>
                                @endif
                                <span class="font-medium text-gray-900">{{ $document->document_number ?? '—' }}</span>
                            </div>
                        </td>

                        <td class="px-3 py-4">
                            <div class="font-medium text-gray-900">
                                {{ $document->from }}
                            </div>
                        </td>

                        <td class="px-3 py-4">
                            <div class="line-clamp-2 text-gray-700" title="{{ $document->subject }}">
                                {{ $document->subject }}
                            </div>
                            @if($showOfficeContext)
                                <div class="mt-1 text-xs text-gray-500">
                                    Receiving office: {{ $document->toOffice?->name ?? '—' }}
                                </div>
                            @endif
                        </td>

                        <td class="px-3 py-4 text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($document->created_at)->format('M d, Y') }}<br>
                            <span class="text-xs">{{ \Carbon\Carbon::parse($document->created_at)->format('h:i A') }}</span>
                        </td>

                        <td class="px-3 py-4 text-right">
                            <flux:button wire:click="viewDocument('{{ $document->id }}')" size="sm" icon="eye" variant="primary" class="px-3 py-1 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white transition-colors">
                                View
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <flux:icon icon="document-magnifying-glass" class="size-10 mb-2 text-gray-300" />
                                <p class="text-base font-medium">No external documents found</p>
                                <p class="text-sm">Try adjusting your search.</p>
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
