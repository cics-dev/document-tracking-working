<section class="w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">External Documents</h2>
        @if(Auth::user()->position == 'Staff')
            <a
                href="{{ route('documents.receive-external-document') }}"
                class="text-sm bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition"
            >
                + Create Document
            </a>
        @endif
    </div>

    <div class="overflow-x-auto rounded-lg shadow-sm bg-white dark:bg-gray-800">
        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
            <thead class="text-xs text-gray-500 uppercase border-b bg-gray-100 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center">From</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center">Subject</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 text-center">Received Date</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents->sortByDesc('created_at') as $index => $document)
                    <tr class="border-b dark:border-gray-600 font-bold">
                        <td class="px-4 py-2 flex items-center gap-2 border-r border-gray-300 dark:border-gray-600">
                            {{ $document->from }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $document->subject }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $document->received_date }}</td>
                        <td class="px-4 py-2">
                            <div class="flex justify-center space-x-2">
                                <button 
                                    wire:click="viewDocument('{{ $document['id'] }}')" 
                                    class="px-3 py-1 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white transition-colors"
                                >
                                    View
                                </button>
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