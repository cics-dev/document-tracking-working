<section class="w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ ucfirst($mode) . ' Documents' }}</h2>
        @if($mode == 'sent')
            <a
                href="{{route('documents.create-document')}}"
                class="text-sm bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition"
            >
                + Add Office
            </a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
            <thead class="text-xs text-gray-500 uppercase border-b dark:text-gray-400 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-2">Document Number</th>
                    <th class="px-4 py-2">Subject</th>
                    <th class="px-4 py-2">From</th>
                    <th class="px-4 py-2">Dcoument Type</th>
                    <th class="px-4 py-2">Date Sent</th>
                    @if($mode == 'sent') <th class="px-4 py-2">Status</th> @endif
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $document)
                    <tr class="border-b dark:border-gray-600">
                        <td class="px-4 py-2">{{ $document->document_number }}</td>
                        <td class="px-4 py-2">{{ $document->subject }}</td>
                        <td class="px-4 py-2">{{ $document->office->name}}</td>
                        <td class="px-4 py-2">{{ $document->document_type->name }}</td>
                        <td class="px-4 py-2">{{ $document->date_sent }}</td>
                        @if($mode == 'sent') <td class="px-4 py-2">{{ $document->status }}</td> @endif
                        <td class="px-4 py-2 space-x-2">
                            <button wire:click="editOffice({{ $document['id'] }})" class="text-[#3366FF] dark:text-[#99BBFF] hover:underline">Edit</button>
                            <button wire:click="deleteOffice('{{ $document['id'] }}')" class="text-[#f53003] dark:text-[#FF4433] hover:underline">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No documents found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
