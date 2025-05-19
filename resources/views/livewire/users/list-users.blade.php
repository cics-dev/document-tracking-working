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

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
            <thead class="text-xs text-gray-500 uppercase border-b dark:text-gray-400 dark:border-gray-600">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Position</th>
                    <th class="px-4 py-2">Office</th>
                    <th class="px-4 py-2">Is Head</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b dark:border-gray-600">
                        <td class="px-4 py-2">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->email }}</td>
                        <td class="px-4 py-2">{{ $user->position }}</td>
                        <td class="px-4 py-2">{{ $user->office->name ?? 'No Office' }}</td>
                        <td class="px-4 py-2">{{ $user->is_head ?? 'No' }}</td>
                        <td class="px-4 py-2 space-x-2">
                            <button wire:click="edituser({{ $user->id }})" class="text-[#3366FF] dark:text-[#99BBFF] hover:underline">Edit</button>
                            <button wire:click="deleteuser('{{ $user->id }}')" class="text-[#f53003] dark:text-[#FF4433] hover:underline">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination Links -->
        <div class="mt-4">
            {{ $users->links() }}
        </div>

    </div>
</section>
