<div class="max-w-5xl px-4 mx-auto container">
    <div class="mb-8">
        <flux:heading size="xl" level="1">Create New User</flux:heading>
        <flux:subheading>Fill in the details below to create a new user account</flux:subheading>
    </div>

    <form wire:submit="saveUser" class="bg-white shadow rounded-lg p-6">
        
        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Personal Information</flux:heading>
            <flux:separator variant="subtle" class="mb-6" />
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                <div class="md:col-span-3">
                    <flux:field>
                        <flux:label>Family Name <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="family_name" type="text" required autofocus />
                        <flux:error name="family_name" />
                    </flux:field>
                </div>
                <div class="md:col-span-3">
                    <flux:field>
                        <flux:label>Given Name <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="given_name" type="text" required />
                        <flux:error name="given_name" />
                    </flux:field>
                </div>
                <div class="md:col-span-3">
                    <flux:input wire:model="middle_name" label="Middle Name" type="text" />
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model="middle_initial" label="MI" type="text" maxlength="1" />
                </div>
                <div class="md:col-span-2">
                    <flux:input wire:model="suffix" label="Suffix" type="text" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-2">
                    <flux:input wire:model="honorifics" label="Honorifics" placeholder="(Mr./Ms.)" type="text" />
                </div>
                <div class="md:col-span-2">
                    <flux:input wire:model="titles" label="Title" placeholder="(PhD, etc.)" type="text" />
                </div>
                <div class="md:col-span-2">
                    <flux:select wire:model="gender" label="Gender" placeholder="Select...">
                        <flux:select.option value="male">Male</flux:select.option>
                        <flux:select.option value="female">Female</flux:select.option>
                        <flux:select.option value="other">Other</flux:select.option>
                    </flux:select>
                </div>
                <div class="md:col-span-6">
                    <flux:field>
                        <flux:label>Email <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="email" type="email" required />
                        <flux:error name="email" />
                    </flux:field>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Professional Information</flux:heading>
            <flux:separator variant="subtle" class="mb-6" />
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <flux:field>
                        <flux:label>Office <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="office_id" placeholder="Choose office...">
                            @foreach ($offices as $office)
                                <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="office_id" />
                    </flux:field>
                </div>
                <div class="md:col-span-4">
                    <flux:field>
                        <flux:label>Position <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="position" type="text" required />
                        <flux:error name="position" />
                    </flux:field>
                </div>
                <div class="md:col-span-4 flex items-end pb-2">
                    <flux:checkbox wire:model="is_head" label="Is head of office" />
                </div>
            </div>
        </div>

        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Profile Signature</flux:heading>
            <flux:separator variant="subtle" class="mb-6" />
            
            <div class="flex flex-col sm:flex-row items-start gap-6">
                <div class="flex-shrink-0 relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Signature</label>
                    <div class="relative group">
                        @if($signature)
                            <img src="{{ $signature->temporaryUrl() }}" class="w-40 h-20 object-contain border-2 border-gray-200 rounded bg-gray-50">
                            <div class="absolute -top-2 -right-2">
                                <flux:button wire:click="$set('signature', null)" icon="x-mark" size="xs" variant="filled" class="rounded-full bg-red-500 hover:bg-red-600 text-white border-none" />
                            </div>
                        @else
                            <div class="w-40 h-20 bg-gray-50 border-2 border-dashed border-gray-300 rounded flex items-center justify-center text-gray-400">
                                <span class="text-xs text-gray-400">No Signature</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="flex-grow">
                    <flux:input type="file" wire:model="signature" label="Upload Signature" description="PNG, JPG (Max: 2MB)" accept="image/png, image/jpeg" />
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <flux:button wire:click="cancel" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Create User</flux:button>
        </div>
    </form>
</div>