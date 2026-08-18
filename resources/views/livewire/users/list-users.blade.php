<section class="w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Users</h2>
        <a
            href="{{route('users.create-user')}}"
            class="text-sm bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition"
        >
            + Add User
        </a>
    </div>

    <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 md:flex-row md:items-center">
        <div class="w-full md:w-80">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search name, email, or position..." />
        </div>
        <div class="w-full md:w-56">
            <x-searchable-filter-select
                model="officeFilter"
                :options="$offices->map(fn ($office) => ['value' => (string) $office->id, 'label' => $office->name, 'search' => $office->abbreviation])->values()->all()"
                placeholder="All Offices"
                search-placeholder="Search offices..."
            />
        </div>
        <div class="w-full md:w-48">
            <x-searchable-filter-select
                model="roleFilter"
                :options="$roles->map(fn ($role) => ['value' => (string) $role->id, 'label' => $role->role])->values()->all()"
                placeholder="All Roles"
                search-placeholder="Search roles..."
            />
        </div>
        <flux:button wire:click="resetFilters" variant="subtle" icon="arrow-path" class="shrink-0">
            Reset filters
        </flux:button>
        <flux:checkbox wire:model.live="showArchived" label="Archived users" />
    </div>
    <flux:error name="archive" />

    <div class="overflow-x-auto rounded-lg shadow-sm bg-white dark:bg-gray-800">
        <!-- Desktop Table -->
        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200 hidden md:table">
            <thead class="text-xs text-gray-500 uppercase border-b bg-gray-100 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600"><button wire:click="sort('name')">Name</button></th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600"><button wire:click="sort('email')">Email</button></th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600"><button wire:click="sort('position')">Position</button></th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">Office</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">Is&nbsp;Head</th>
                    <th class="px-4 py-2 text-center border-r border-gray-300 dark:border-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->name }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->position }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->office->name ?? 'No Office' }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->is_head ?? 'No' }}</td>
                        <td class="px-4 py-2 text-center border-r border-gray-300 dark:border-gray-600">
                            <div class="flex justify-center">
                                <div class="relative" x-data="{ open: false }">
                                    <div class="flex justify-center">
                                        <button 
                                            @click="open = !open" 
                                            class="text-gray-600 focus:outline-none transition-all duration-200 rounded-full p-1 hover:bg-gray-200 hover:shadow-sm dark:hover:bg-gray-600"
                                        >
                                            <img src="https://cdn-icons-png.flaticon.com/128/5972/5972963.png" alt="Edit" class="h-6 w-6 hover:scale-140 transition-transform">
                                        </button>
                                    </div>
                                    
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
                                            <button wire:click="restoreUser({{ $user->id }})" wire:confirm="Restore this user account?" class="flex items-center w-full px-4 py-2 text-left text-sm text-blue-700 hover:bg-blue-100">
                                               <b>Restore</b>
                                            </button>
                                            @else
                                            <button wire:click="editUser({{ $user->id }})" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-green-200 dark:hover:bg-green-900 hover:text-green-700 dark:hover:text-green-200 transition-colors">
                                                <img src="https://cdn-icons-png.flaticon.com/128/12493/12493756.png" alt="Edit" class="h-4 w-4 mr-2">
                                               <b> Edit </b>
                                            </button>
                                            <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Deactivate this user? Historical documents will be retained." class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-red-200 dark:hover:bg-red-900 hover:text-red-700 dark:hover:text-red-200 transition-colors">
                                                <img src="https://cdn-icons-png.flaticon.com/128/11641/11641591.png" alt="Delete" class="h-4 w-4 mr-2">
                                               <b> Deactivate </b>
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
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Mobile Card Layout -->
        <div class="md:hidden space-y-4 p-2">
            @forelse ($users as $user)
                <div class="border rounded-lg p-4 shadow-sm bg-white dark:bg-gray-700 dark:border-gray-600">
                    <div class="mb-2"><strong class="text-gray-700 dark:text-gray-200">Name:</strong> <span class="text-gray-600 dark:text-gray-300">{{ $user->name }}</span></div>
                    <div class="mb-2"><strong class="text-gray-700 dark:text-gray-200">Email:</strong> <span class="text-gray-600 dark:text-gray-300">{{ $user->email }}</span></div>
                    <div class="mb-2"><strong class="text-gray-700 dark:text-gray-200">Position:</strong> <span class="text-gray-600 dark:text-gray-300">{{ $user->position }}</span></div>
                    <div class="mb-2"><strong class="text-gray-700 dark:text-gray-200">Office:</strong> <span class="text-gray-600 dark:text-gray-300">{{ $user->office->name ?? 'No Office' }}</span></div>
                    <div class="mb-4"><strong class="text-gray-700 dark:text-gray-200">Is Head:</strong> <span class="text-gray-600 dark:text-gray-300">{{ $user->is_head ?? 'No' }}</span></div>

                    <!-- Inline Action Buttons for Mobile -->
                    <div class="flex justify-end space-x-2 mt-2">
                        @if($showArchived)
                            <button wire:click="restoreUser({{ $user->id }})" wire:confirm="Restore this user account?" class="rounded-md bg-blue-500 px-3 py-1 text-white">Restore</button>
                        @else
                            <button wire:click="editUser({{ $user->id }})" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 transition">Edit</button>
                            <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Deactivate this user? Historical documents will be retained." class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition">Deactivate</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400 py-4">No users found.</div>
            @endforelse
        </div>
        <!-- End Mobile View -->

        <!-- Pagination Links - Updated to match offices table styling -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
            {{ $users->links() }}
        </div>
    </div>
</section>
