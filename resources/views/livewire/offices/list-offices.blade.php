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
                          <!--  <div class="flex justify-center space-x-2">
                                <button wire:click="editOffice({{ $office['id'] }})" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 transition">Edit</button>
                                <button wire:click="deleteOffice('{{ $office['id'] }}')" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition">Delete</button>
                            </div> -->

    <!---END ICON WITH HOVER--->
                            <div class="flex justify-center">
    <div class="relative" x-data="{ open: false }">
        <!-- Edit icon button with hover effect -->
        <div class="flex justify-center">
            <button 
                @click="open = !open" 
                class="text-gray-600 focus:outline-none transition-all duration-200 rounded-full p-1 hover:bg-gray-200 hover:shadow-sm"
            >
                <img src="https://cdn-icons-png.flaticon.com/128/5972/5972963.png" alt="Edit" class="h-6 w-6 hover:scale-140 transition-transform">
            </button>
        </div>
        
        <!-- Centered dropdown menu -->
        <div x-show="open" 
             @click.away="open = false"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute left-1/2 transform -translate-x-1/2 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-100">
            <div class="py-1">
                <button wire:click="editOffice({{ $office['id'] }})" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-900 hover:bg-green-200 hover:text-green-700 transition-colors">
    <!-- Changed from SVG to image icon -->
                    <img src="https://cdn-icons-png.flaticon.com/128/12493/12493756.png" alt="Edit" class="h-4 w-4 mr-2">
                        <b> Edit </b>
                        </button>
                <button wire:click="deleteOffice({{ $office['id'] }})" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-900 hover:bg-red-200 hover:text-red-700 transition-colors">
    <!-- Changed from SVG to image icon -->
                    <img src="https://cdn-icons-png.flaticon.com/128/11641/11641591.png" alt="Edit" class="h-4 w-4 mr-2">
                        <b> Delete </b>
                        </button>
            </div>
        </div>
    </div>
</div>
<!---END ICON WITH HOVER--->

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
