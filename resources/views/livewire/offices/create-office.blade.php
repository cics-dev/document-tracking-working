<<<<<<< HEAD
<div class="max-w-5xl px-4 mx-auto container">
    <div class="mb-8">
        <flux:heading size="xl" level="1">{{ $edit_mode ? 'Edit Office' : 'Create New Office' }}</flux:heading>
        <flux:subheading>Fill in the details below to {{ $edit_mode ? 'edit' : 'create new' }} office</flux:subheading>
    </div>

    <form wire:submit="saveOffice" class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6">
        
        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Office Information</flux:heading>
            <flux:separator variant="subtle" class="mb-6" />
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-8">
                    <flux:field>
                        <flux:label>Office Name <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="name" type="text" required autofocus autocomplete="name" />
                        <flux:error name="name" />
                    </flux:field>
                </div>
                <div class="md:col-span-4">
                    <flux:field>
                        <flux:label>Abbreviation <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="abbreviation" type="text" required autocomplete="abbreviation" />
                        <flux:error name="abbreviation" />
                    </flux:field>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Management Details</flux:heading>
            <flux:separator variant="subtle" class="mb-6" />
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-6">
                    <flux:field>
                        <flux:label>Type of Office</flux:label>
                        <flux:select wire:model="office_type" placeholder="Choose office type...">
                            <flux:select.option value="ACAD">Academic</flux:select.option>
                            <flux:select.option value="ADMIN">Administration</flux:select.option>
                        </flux:select>
                        <flux:error name="office_type" />
                    </flux:field>
                </div>
                
                <div class="md:col-span-6">
                    <flux:select wire:model="office_head" label="Head of Office" placeholder="Choose office head...">
                        @foreach ($users as $user)
                            <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Office Branding</flux:heading>
            <flux:separator variant="subtle" class="mb-6" />
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <div class="flex-shrink-0 relative">
                    @if($office_logo)
                        <img src="{{ $office_logo->temporaryUrl() }}" class="w-24 h-24 object-cover border-2 border-gray-300 dark:border-zinc-600 rounded-full">
                        <div class="absolute -top-2 -right-2">
                             <flux:button wire:click="$set('office_logo', null)" icon="x-mark" size="xs" variant="filled" class="rounded-full bg-red-500 hover:bg-red-600 text-white border-none" />
                        </div>
                    @else
                        <div class="w-24 h-24 bg-gray-50 dark:bg-zinc-700 border-2 border-dashed border-gray-200 dark:border-zinc-600 rounded-full flex items-center justify-center text-gray-400 dark:text-zinc-400">
                            <flux:icon icon="photo" class="size-8" />
                        </div>
                    @endif
                </div>
                
                <div class="flex-grow">
                    <flux:input type="file" wire:model="office_logo" label="Upload Logo" description="PNG, JPG (Max: 2MB)" />
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-zinc-700">
            <flux:button wire:click="cancel" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">{{ $edit_mode ? 'Update' : 'Create' }} Office</flux:button>
        </div>
    </form>
</div>
=======
<style>
/* Your custom form style */
.custom-form-container {
  max-width: 800px;
  background-color: #ffffff;
  border-radius: 1rem;
  padding: 2rem;
  margin: 1rem auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.custom-form-container h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 1rem;
}

.custom-form-container label {
  color: #4a5568;
  font-weight: 500;
  margin-bottom: 0.5rem;
  display: block;
}

.custom-button {
  background-color: #2563eb;
  color: white;
  padding: 0.6rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: background-color 0.2s ease;
}

.custom-button:hover {
  background-color: #0000FF;
}

@media (max-width: 740px) {
  .form-grid {
    flex-direction: column;
  }
}
/* Uiverse.io card and avatar styles – not removed */
.card {
  --p: 32px;
  --h-form: auto;
  --w-form: 1000px;
  --input-px: 0.75rem;
  --input-py: 0.65rem;
  --submit-h: 38px;
  --blind-w: 64px;
  --space-y: 0.5rem;
  width: var(--w-form);
  height: var(--h-form);
  max-width: 100%;
  border-radius: 16px;
  background: white;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-evenly;
  flex-direction: column;
  overflow-y: auto;
  padding: var(--p);
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
  -webkit-font-smoothing: antialiased;
  -webkit-user-select: none;
  user-select: none;
  font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
    "Lucida Sans", Arial, sans-serif;
}
</style>
<div class="container mx-auto px-4 custom-form-container">
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
            <button type="submit" class="custom-button">
                Save
            </button>
        </div>
    </form>
</div>
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
