<section class="w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Offices</h2>
        <a
            href="{{route('offices.create-office')}}"
            class="text-sm bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition"
        >
            + Add Office
        </a>

    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
            <thead class="text-xs text-gray-500 uppercase border-b dark:text-gray-400 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Abbreviation</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Office Head</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($offices as $office)
                    <tr class="border-b dark:border-gray-600">
                        <td class="px-4 py-2">{{ $office->name }}</td>
                        <td class="px-4 py-2">{{ $office->abbreviation }}</td>
                        <td class="px-4 py-2">{{ $office->office_type }}</td>
                        <td class="px-4 py-2">{{ $office->head->name??'Not set' }}</td>
                        <td class="px-4 py-2 space-x-2">
                            <button wire:click="editOffice({{ $office['id'] }})" class="text-[#3366FF] dark:text-[#99BBFF] hover:underline">Edit</button>
                            <button wire:click="deleteOffice('{{ $office['id'] }}')" class="text-[#f53003] dark:text-[#FF4433] hover:underline">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No offices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
