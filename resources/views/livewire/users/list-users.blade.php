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

    <div class="overflow-x-auto rounded-lg shadow-sm bg-white dark:bg-gray-800">
        <!-- Desktop Table -->
        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200 hidden md:table">
            <thead class="text-xs text-gray-500 uppercase border-b bg-gray-100 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">Name</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">Email</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">Position</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">Office</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">Is&nbsp;Head</th>
                    <th class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->name }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->position }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->office->name??'No Office' }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">{{ $user->is_head??'No' }}</td>
                        <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600">
                            <div class="flex items-center space-x-2">
                                <button wire:click="edituser({{ $user['id'] }})" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 transition">
                                    Edit
                                </button>
                                <button wire:click="deleteuser('{{ $user['id'] }}')" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition">
                                    Delete
                                </button>
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
                    <div class="mb-2"><strong>Name:</strong> {{ $user->name }}</div>
                    <div class="mb-2"><strong>Email:</strong> {{ $user->email }}</div>
                    <div class="mb-2"><strong>Position:</strong> {{ $user->position }}</div>
                    <div class="mb-2"><strong>Office:</strong> {{ $user->office->name ?? 'No Office' }}</div>
                    <div class="mb-4"><strong>Is Head:</strong> {{ $user->is_head ?? 'No' }}</div>
                    <div class="flex justify-end space-x-2">
                        <button wire:click="edituser({{ $user['id'] }})" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 transition">Edit</button>
                        <button wire:click="deleteuser('{{ $user['id'] }}')" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition">Delete</button>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400">No users found.</div>
            @endforelse
        </div>
        <!-- End Mobile View -->
    </div>
</section>
