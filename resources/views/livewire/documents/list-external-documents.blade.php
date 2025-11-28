<section class="w-full space-y-6">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">External Documents</flux:heading>
            <flux:subheading>Manage documents received from outside agencies.</flux:subheading>
        </div>

        @if(Auth::user()->position == 'Staff')
            <flux:button href="{{ route('documents.receive-external-document') }}" variant="primary" icon="plus" class="w-full md:w-auto">
                Receive Document
            </flux:button>
        @endif
    </div>

    <div class="flex flex-col md:flex-row gap-4 justify-between items-center bg-gray-50 p-4 rounded-lg border border-gray-200">
        <div class="w-full md:w-80">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search subject or sender..." />
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200 bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3">Document Number</th>
                    <th class="px-6 py-3">From</th>
                    <th class="px-6 py-3 w-1/3">Subject</th>
                    <th class="px-6 py-3">Received Date</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($documents as $document)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if(!$document->is_viewed_by_me)
                                    <span class="size-2 rounded-full bg-blue-600 animate-pulse"></span>
                                @endif
                                <span class="font-medium text-gray-900">{{ $document->document_number ?? '—' }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">
                                {{ $document->from }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="line-clamp-2 text-gray-700" title="{{ $document->subject }}">
                                {{ $document->subject }}
                            </div>
                        </td>

                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($document->received_date)->format('M d, Y') }}<br>
                            <span class="text-xs">{{ \Carbon\Carbon::parse($document->received_date)->format('h:i A') }}</span>
                        </td>

                        <td class="px-6 py-4 text-right">
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