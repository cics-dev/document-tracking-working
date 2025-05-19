<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Create New Office</h1>
    <form wire:submit.prevent="saveOffice" class="mt-6 space-y-6">
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

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                {{ __('Office Logo') }}
            </label>
            <div class="mt-1 flex items-center">
                <!-- Image Preview -->
                @if($office_logo)
                    <div class="mr-4 flex-shrink-0">
                        <img class="h-16 w-16 rounded-full object-cover" src="{{ $office_logo->temporaryUrl() }}" alt="Office image preview">
                    </div>
                @endif
                <label class="cursor-pointer">
                    <span class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ $office_logo ? 'Change Image' : 'Upload Image' }}
                    </span>
                    <input type="file" wire:model="office_logo" class="sr-only" accept="image/*">
                </label>
                @if($office_logo)
                    <button type="button" wire:click="$set('office_logo', null)" class="ml-2 text-sm text-red-600 hover:text-red-500">
                        Remove
                    </button>
                @endif
            </div>
            @error('office_logo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            <p class="text-xs text-gray-500 mt-1">
                JPEG, PNG, or GIF (Max: 2MB)
            </p>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Save
            </button>
        </div>
    </form>
</div>
