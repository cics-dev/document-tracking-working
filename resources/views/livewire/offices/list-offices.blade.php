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

    <div class="overflow-x-auto rounded-lg shadow-sm bg-white dark:bg-gray-800">
        <table class="border-spacing-y-2 text-sm text-left text-gray-700 dark:text-gray-200 w-full hidden md:table">
            <thead class="text-xs text-gray-500 uppercase border-b bg-gray-100 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                <tr>  
                    <th class="px-4 py-3 border-r border-gray-300 dark:border-gray-600">Name</th>
                    <th class="px-4 py-3 border-r border-gray-300 dark:border-gray-600">Abbreviation</th>
                    <th class="px-4 py-3 border-r border-gray-300 dark:border-gray-600">Type</th>
                    <th class="px-4 py-3 border-r border-gray-300 dark:border-gray-600">Office Head</th>
                    <th class="px-4 py-3 text-center border-r border-gray-300 dark:border-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($offices as $office)
                    <tr class="border-b dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-4 py-3 border-r border-gray-300 dark:border-gray-600">{{ $office->name }}</td>
                        <td class="px-4 py-3 border-r border-gray-300 dark:border-gray-600">{{ $office->abbreviation }}</td>
                        <td class="px-4 py-3 border-r border-gray-300 dark:border-gray-600">{{ $office->office_type }}</td>
                        <td class="px-4 py-3 border-r border-gray-300 dark:border-gray-600">{{ $office->head->name??'Not set' }}</td>
                        <td class="px-4 py-3 text-center border-r border-gray-300 dark:border-gray-600">
                            <div class="flex justify-center space-x-2">
                                <button wire:click="editOffice({{ $office['id'] }})" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 transition">Edit</button>
                                <button wire:click="deleteOffice('{{ $office['id'] }}')" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No offices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Mobile View (Only Additions Below) -->
        <div class="md:hidden space-y-4 p-2">
            @forelse ($offices as $office)
                <div class="border rounded-lg p-4 shadow-sm bg-white dark:bg-gray-700 dark:border-gray-600">
                    <div class="mb-2"><strong>Name:</strong> {{ $office->name }}</div>
                    <div class="mb-2"><strong>Abbreviation:</strong> {{ $office->abbreviation }}</div>
                    <div class="mb-2"><strong>Type:</strong> {{ $office->office_type }}</div>
                    <div class="mb-4"><strong>Office Head:</strong> {{ $office->head->name ?? 'Not set' }}</div>
                    <div class="flex justify-end space-x-2">
                        <button wire:click="editOffice({{ $office['id'] }})" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 transition">Edit</button>
                        <button wire:click="deleteOffice('{{ $office['id'] }}')" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition">Delete</button>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400">No offices found.</div>
            @endforelse
        </div>
        <!-- End Mobile View -->
    </div>
</section>
