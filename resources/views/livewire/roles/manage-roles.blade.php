<div class="mx-auto max-w-6xl p-6">
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">Roles</flux:heading>
            <flux:subheading>Create and maintain the roles used by Access Rights.</flux:subheading>
        </div>
        @if(auth()->user()->hasAccess('manage_access_rights'))
            <flux:button :href="route('access-rights')" wire:navigate variant="subtle" icon="key">Access Rights</flux:button>
        @endif
    </div>

    @if (session('status')) <div class="mb-4 rounded bg-green-100 p-3 text-green-800">{{ session('status') }}</div> @endif
    @error('delete') <div class="mb-4 rounded bg-red-100 p-3 text-red-800">{{ $message }}</div> @enderror

    <div class="grid gap-6 lg:grid-cols-5">
        <form wire:submit="save" class="h-fit rounded-lg border bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="mb-4 font-semibold">{{ $roleId ? 'Edit Role' : 'New Role' }}</h2>
            <div class="space-y-4">
                <flux:field><flux:label>Role key <span class="text-red-500">*</span></flux:label><flux:input wire:model="role" required placeholder="e.g. records-officer" /><flux:error name="role" /></flux:field>
                <flux:error name="role" />
                <flux:field><flux:label>Display name <span class="text-red-500">*</span></flux:label><flux:input wire:model="description" required placeholder="e.g. Records Officer" /><flux:error name="description" /></flux:field>
                <flux:error name="description" />
                <x-role-access-editor :$permissions :$types />
                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">{{ $roleId ? 'Update Role' : 'Create Role' }}</flux:button>
                    @if($roleId)<flux:button type="button" wire:click="resetForm" variant="subtle">Cancel</flux:button>@endif
                </div>
            </div>
        </form>

        <section class="lg:col-span-3">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search roles..." class="mb-4" />
            <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 text-xs uppercase text-gray-600"><tr><th class="p-3">Role</th><th class="p-3">Description</th><th class="p-3">Users</th><th class="p-3 text-right">Actions</th></tr></thead>
                    <tbody>
                        @forelse($roles as $item)
                            <tr class="border-t">
                                <td class="p-3 font-mono">{{ $item->role }}</td>
                                <td class="p-3">{{ $item->description }}</td>
                                <td class="p-3">{{ $item->users_count }}</td>
                                <td class="p-3"><div class="flex justify-end gap-2">
                                    <flux:button wire:click="edit({{ $item->id }})" size="sm" variant="subtle">Edit</flux:button>
                                    <flux:button wire:click="delete({{ $item->id }})" wire:confirm="Delete this role?" size="sm" variant="danger" :disabled="$item->users_count > 0">Delete</flux:button>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-6 text-center text-gray-500">No roles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="border-t p-3">{{ $roles->links() }}</div>
            </div>
        </section>
    </div>
</div>
