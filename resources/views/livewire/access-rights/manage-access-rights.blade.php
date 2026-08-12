<div class="mx-auto max-w-7xl p-6">
    <div class="flex items-start justify-between gap-4">
        <div><flux:heading size="xl">Access Rights</flux:heading>
        <flux:subheading>Assign system capabilities and available document types by role.</flux:subheading></div>
        <flux:button :href="route('roles')" wire:navigate variant="subtle" icon="user-group">Manage Roles</flux:button>
    </div>
    @if (session('status')) <div class="my-4 rounded bg-green-100 p-3 text-green-800">{{ session('status') }}</div> @endif
    <form wire:submit="save" class="mt-6 space-y-5">
        @foreach ($roles as $role)
            <section class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="font-semibold">{{ $role->description }} <span class="text-sm font-normal text-gray-500">({{ $role->role }})</span></h2>
                <div class="mt-4 grid gap-6 md:grid-cols-2">
                    <div><h3 class="mb-2 text-sm font-medium">System rights</h3>
                        @foreach ($permissions as $permission)
                            <label class="mb-2 flex gap-2"><input type="checkbox" value="{{ $permission->id }}" wire:model="rights.{{ $role->id }}"> {{ $permission->label }}</label>
                        @endforeach
                    </div>
                    <div><h3 class="mb-2 text-sm font-medium">Can create document types</h3>
                        @foreach ($types as $type)
                            <label class="mb-2 flex gap-2"><input type="checkbox" value="{{ $type->id }}" wire:model="documentTypes.{{ $role->id }}"> {{ $type->name }}</label>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach
        <flux:button type="submit" variant="primary">Update Access Rights</flux:button>
    </form>
</div>
