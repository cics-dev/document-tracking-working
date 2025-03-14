<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Create New Office</h1>
    <form wire:submit.prevent="submitDocument" class="mt-6 space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />
        <div class="flex gap-4">
            <div class="flex-1">
                <flux:input wire:model="abbreviation" :label="__('Abbreviation')" type="text" required autocomplete="abbreviation" />
            </div>
            <div class="flex-1 space-y-1.5">
                <label class="block text-sm font-medium text-gray-700">
                    {{ __('Type of Office') }}
                </label>

                <flux:select wire:model="office_type" placeholder="Choose office type...">
                    <flux:select.option value="ACAD">Academic</flux:select.option>
                    <flux:select.option value="ADMIN">Administration</flux:select.option>
                </flux:select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                {{ __('Head of Office') }}
            </label>

            <flux:select wire:model="office_head" placeholder="Choose office head...">
                @foreach ($users as $user)
                    <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div class="mt-4 flex justify-end">
            <button type="button" wire:click.prevent="saveOffice" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Save
            </button>
        </div>
    </form>
</div>  