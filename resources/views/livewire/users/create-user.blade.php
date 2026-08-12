<div class="container mx-auto max-w-5xl px-4">
    <div class="mb-8">
        <flux:heading size="xl" level="1">{{ $editMode ? 'Edit User' : 'Create New User' }}</flux:heading>
        <flux:subheading>Fill in the details below to {{ $editMode ? 'edit' : 'create a new' }} user account.</flux:subheading>
    </div>
    <form wire:submit="saveUser" class="rounded-lg bg-white p-6 shadow">
        <x-user-account-fields :$offices :$roles :existing-signature="$existingSignature" />
        <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
            <flux:button type="button" wire:click="cancel" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">{{ $editMode ? 'Update User' : 'Create User' }}</flux:button>
        </div>
    </form>
</div>
