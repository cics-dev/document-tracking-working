<section class="w-full space-y-6">
    <style>
        @media (max-width: 767px) {
            .document-table thead {
                display: none;
            }

            .document-table,
            .document-table tbody,
            .document-table tr,
            .document-table td {
                display: block;
                width: 100%;
            }

            .document-table tr:not(.document-empty-row) {
                padding: .75rem;
            }

            .document-table td:not(.document-empty-cell) {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                padding: .5rem .25rem;
                text-align: right;
            }

            .document-table td:not(.document-empty-cell)::before {
                flex-shrink: 0;
                color: #94a3b8;
                content: attr(data-label);
                font-size: .65rem;
                font-weight: 700;
                letter-spacing: .05em;
                text-align: left;
                text-transform: uppercase;
            }

            .document-table .document-empty-cell {
                min-height: 16rem;
            }
        }
    </style>
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">External Documents</flux:heading>
            <flux:subheading>Manage documents received from outside agencies.</flux:subheading>
        </div>

        {{-- @if(Auth::user()->position == 'Staff') --}}
            <flux:button href="{{ route('documents.receive-external-document') }}" variant="primary" icon="plus" class="w-full md:w-auto">
                Receive Document
            </flux:button>
        {{-- @endif --}}
    </div>

    <div class="flex flex-col md:flex-row gap-4 justify-between items-center bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg border border-gray-200 dark:border-zinc-700">
        <div class="w-full md:w-80">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search subject or sender..." />
        </div>
    </div>

    <div class="overflow-hidden rounded-xl shadow-sm border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="document-table w-full text-sm text-left text-gray-900 dark:text-zinc-100">
            <thead class="bg-gray-50 dark:bg-zinc-800 text-gray-500 dark:text-zinc-300 font-medium border-b border-gray-200 dark:border-zinc-700">
                <tr>
                    <th class="px-6 py-3">Document Number</th>
                    <th class="px-6 py-3">From</th>
                    <th class="px-6 py-3 w-1/3">Subject</th>
                    <th class="px-6 py-3">Received Date</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                @forelse ($documents as $document)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/70 transition-colors">
                        
                        <td data-label="Document Number" class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if(!$document->is_viewed_by_me)
                                    <span class="size-2 rounded-full bg-blue-600 animate-pulse"></span>
                                @endif
                                <span class="font-medium text-gray-900 dark:text-zinc-100">{{ $document->document_number ?? '—' }}</span>
                            </div>
                        </td>

                        <td data-label="From" class="px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-zinc-100">
                                {{ $document->from }}
                            </div>
                        </td>

                        <td data-label="Subject" class="px-6 py-4">
                            <div class="line-clamp-2 text-gray-700 dark:text-zinc-200" title="{{ $document->subject }}">
                                {{ $document->subject }}
                            </div>
                        </td>

                        <td data-label="Received Date" class="px-6 py-4 text-gray-500 dark:text-zinc-300 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($document->created_at)->format('M d, Y') }}<br>
                            <span class="text-xs">{{ \Carbon\Carbon::parse($document->created_at)->format('h:i A') }}</span>
                        </td>

                        <td data-label="Actions" class="px-6 py-4 text-right">
                            <flux:button wire:click="viewDocument('{{ $document->id }}')" size="sm" icon="eye" variant="primary" class="px-3 py-1 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white transition-colors">
                                View
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr class="document-empty-row">
                        <td colspan="5" class="document-empty-cell h-[50vh] px-6 py-12 text-center align-middle">
                            <div class="flex min-h-[35vh] flex-col items-center justify-center text-gray-500 dark:text-zinc-300">
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