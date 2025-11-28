<div class="max-w-5xl px-4 mx-auto container">
    <div class="mb-8">
        <flux:heading size="xl" level="1">Create New Office</flux:heading>
        <flux:subheading>Fill in the details below to create a new office</flux:subheading>
    </div>

    <form wire:submit="saveOffice" class="bg-white shadow rounded-lg p-6">
        
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
                        <flux:label>Type of Office <span class="text-red-500">*</span></flux:label>
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
                        <img src="{{ $office_logo->temporaryUrl() }}" class="w-24 h-24 object-cover border-2 border-gray-300 rounded-full">
                        <div class="absolute -top-2 -right-2">
                             <flux:button wire:click="$set('office_logo', null)" icon="x-mark" size="xs" variant="filled" class="rounded-full bg-red-500 hover:bg-red-600 text-white border-none" />
                        </div>
                    @else
                        <div class="w-24 h-24 bg-gray-50 border-2 border-dashed border-gray-200 rounded-full flex items-center justify-center text-gray-400">
                            <flux:icon icon="photo" class="size-8" />
                        </div>
                    @endif
                </div>
                
                <div class="flex-grow">
                    <flux:input type="file" wire:model="office_logo" label="Upload Logo" description="PNG, JPG (Max: 2MB)" />
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <flux:button wire:click="cancel" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Create Office</flux:button>
        </div>
    </form>
</div>