<div class="mx-auto max-w-7xl p-6">
    <div class="flex items-start justify-between gap-4">
        <div><flux:heading size="xl">Access Rights</flux:heading>
        <flux:subheading>Assign system capabilities and available document types by role.</flux:subheading></div>
        @if(auth()->user()->hasAccess('manage_roles'))
            <flux:button :href="route('roles')" wire:navigate variant="subtle" icon="user-group">Manage Roles</flux:button>
        @endif
    </div>
    @if (session('status')) <div class="my-4 rounded bg-green-100 p-3 text-green-800">{{ session('status') }}</div> @endif
    <div class="mt-6 space-y-5">
        @foreach ($roles as $role)
            <section class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="font-semibold">{{ $role->description }} <span class="text-sm font-normal text-gray-500">({{ $role->role }})</span></h2>
                <div class="mt-4">
                    <x-role-access-editor :$permissions :$types rights-model="rights.{{ $role->id }}" document-types-model="documentTypes.{{ $role->id }}" error-name="rights.{{ $role->id }}" />
                </div>
                <div class="mt-4 flex justify-end">
                    <flux:button type="button" wire:click="saveRole({{ $role->id }})" wire:loading.attr="disabled" wire:target="saveRole({{ $role->id }})" variant="primary">
                        Update {{ $role->description }}
                    </flux:button>
                </div>
            </section>
        @endforeach
    </div>
</div>
