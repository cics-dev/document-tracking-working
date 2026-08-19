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

    <div class="mb-4 flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 md:flex-row md:items-center">
        <div class="w-full md:w-80">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search office or abbreviation..." />
        </div>
        <div class="w-full md:w-48">
            <flux:select wire:model.live="typeFilter" placeholder="All office types">
                <flux:select.option value="ACAD">Academic</flux:select.option>
                <flux:select.option value="ADMIN">Administration</flux:select.option>
            </flux:select>
        </div>
        <flux:button wire:click="resetFilters" variant="subtle" icon="arrow-path" class="shrink-0">
            Reset filters
        </flux:button>
        <flux:checkbox wire:model.live="showArchived" label="Archived offices" />
    </div>
    <flux:error name="archive" />

    <div class="overflow-x-auto rounded-lg shadow-sm bg-white dark:bg-gray-800">
        <table class="border-spacing-y-2 text-sm text-left text-gray-700 dark:text-gray-200 w-full hidden md:table">
            <thead class="text-xs text-gray-500 uppercase border-b bg-gray-100 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                <tr>  
                    <th class="px-4 py-3 border-r border-gray-300 dark:border-gray-600"><button wire:click="sort('name')">Name</button></th>
                    <th class="px-4 py-3 border-r border-gray-300 dark:border-gray-600"><button wire:click="sort('abbreviation')">Abbreviation</button></th>
                    <th class="px-4 py-3 border-r border-gray-300 dark:border-gray-600"><button wire:click="sort('office_type')">Type</button></th>
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
                            <div class="flex justify-center">
                                <div class="relative" x-data="{ open: false }">
                                    <!-- Edit icon button with hover effect -->
                                    <div class="flex justify-center">
                                        <button 
                                            @click="open = !open" 
                                            class="text-gray-600 focus:outline-none transition-all duration-200 rounded-full p-1 hover:bg-gray-200 hover:shadow-sm"
                                        >
                                            <img src="https://cdn-icons-png.flaticon.com/128/5972/5972963.png" alt="Edit" class="h-6 w-6 hover:scale-110 transition-transform">
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
                                         class="absolute left-1/2 transform -translate-x-1/2 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg z-10 border border-gray-100 dark:border-gray-600">
                                        <div class="py-1">
                                            @if($showArchived)
                                            <button wire:click="restoreOffice({{ $office->id }})" wire:confirm="Restore this office?" class="flex w-full items-center px-4 py-2 text-left text-sm text-blue-700 hover:bg-blue-100"><b>Restore</b></button>
                                            @else
                                            <button wire:click="editOffice({{ $office->id }})" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-900 dark:text-gray-200 hover:bg-green-200 dark:hover:bg-green-700 hover:text-green-700 dark:hover:text-green-100 transition-colors">
                                                <img src="https://cdn-icons-png.flaticon.com/128/12493/12493756.png" alt="Edit" class="h-4 w-4 mr-2">
                                                <b>Edit</b>
                                            </button>
                                            <button wire:click="deleteOffice({{ $office->id }})" wire:confirm="Deactivate this office? Historical documents will be retained." class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-900 dark:text-gray-200 hover:bg-red-200 dark:hover:bg-red-700 hover:text-red-700 dark:hover:text-red-100 transition-colors">
                                                <img src="https://cdn-icons-png.flaticon.com/128/11641/11641591.png" alt="Delete" class="h-4 w-4 mr-2">
                                                <b>Deactivate</b>
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
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

        <!-- Mobile View -->
        <div class="md:hidden space-y-4 p-2">
            @forelse ($offices as $office)
                <div class="border rounded-lg p-4 shadow-sm bg-white dark:bg-gray-700 dark:border-gray-600">
                    <div class="mb-2"><strong class="text-gray-700 dark:text-gray-200">Name:</strong> <span class="text-gray-600 dark:text-gray-300">{{ $office->name }}</span></div>
                    <div class="mb-2"><strong class="text-gray-700 dark:text-gray-200">Abbreviation:</strong> <span class="text-gray-600 dark:text-gray-300">{{ $office->abbreviation }}</span></div>
                    <div class="mb-2"><strong class="text-gray-700 dark:text-gray-200">Type:</strong> <span class="text-gray-600 dark:text-gray-300">{{ $office->office_type }}</span></div>
                    <div class="mb-4"><strong class="text-gray-700 dark:text-gray-200">Office Head:</strong> <span class="text-gray-600 dark:text-gray-300">{{ $office->head->name ?? 'Not set' }}</span></div>
                    <div class="flex justify-end space-x-2">
                        @if($showArchived)
                            <button wire:click="restoreOffice({{ $office->id }})" wire:confirm="Restore this office?" class="rounded-md bg-blue-500 px-3 py-1 text-white">Restore</button>
                        @else
                            <button wire:click="editOffice({{ $office->id }})" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 transition">Edit</button>
                            <button wire:click="deleteOffice({{ $office->id }})" wire:confirm="Deactivate this office? Historical documents will be retained." class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition">Deactivate</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400 py-4">No offices found.</div>
            @endforelse
        </div>
        <!-- End Mobile View -->

        <!-- Pagination Links -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
            {{ $offices->links() }}
        </div>
    </div>
</section>
