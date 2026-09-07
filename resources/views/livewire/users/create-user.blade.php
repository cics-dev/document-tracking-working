<<<<<<< HEAD
<div class="max-w-5xl px-4 mx-auto container">
    <div class="mb-8">
        <flux:heading size="xl" level="1">{{ $editMode ? 'Edit User' : 'Create New User' }}</flux:heading>
        <flux:subheading>Fill in the details below to {{ $editMode ? 'edit' : 'create new' }} user account</flux:subheading>
    </div>

    <form wire:submit="saveUser" class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6">
        
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
=======
<div class="container mx-auto px-4 max-w-5xl">
    <!-- Header Section -->
    <div class="mb-8 border-b border-gray-200 pb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create New User</h1>
        <p class="mt-2 text-sm text-gray-600">Fill in the details below to create a new user account</p>
    </div>

    <!-- Form Section -->
    <form wire:submit.prevent="submitDocument" class="bg-white shadow rounded-lg p-6">
        <!-- Personal Information Section -->
        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Personal Information</h2>
            
            <!-- Name Row -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                <div class="md:col-span-3">
                    <flux:input wire:model="family_name" :label="__('Family Name')" type="text" required autofocus autocomplete="family_name" class="w-full" />
                </div>
                <div class="md:col-span-3">
                    <flux:input wire:model="given_name" :label="__('Given Name')" type="text" required autocomplete="given_name" class="w-full" />
                </div>
                <div class="md:col-span-3">
                    <flux:input wire:model="middle_name" :label="__('Middle Name')" type="text" autocomplete="middle_name" class="w-full" />
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model="middle_initial" :label="__('MI')" type="text" maxlength="1" autocomplete="middle_initial" class="w-full" />
                </div>
                <div class="md:col-span-2">
                    <flux:input wire:model="suffix" :label="__('Suffix')" type="text" autocomplete="suffix" class="w-full" />
                </div>
            </div>

            <!-- Details Row -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-2">
                    <flux:input wire:model="honorifics" :label="__('Honorifics')" type="text" autocomplete="honorifics" class="w-full" />
                </div>
                <div class="md:col-span-2">
                    <flux:input wire:model="titles" :label="__('Titles')" type="text" autocomplete="titles" class="w-full" />
                </div>
                <div class="md:col-span-2">
                    <flux:select wire:model="gender" :label="__('Gender')" class="w-full">
                        <flux:select.option value="">Select...</flux:select.option>
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
                        <flux:select.option value="male">Male</flux:select.option>
                        <flux:select.option value="female">Female</flux:select.option>
                        <flux:select.option value="other">Other</flux:select.option>
                    </flux:select>
                </div>
                <div class="md:col-span-6">
<<<<<<< HEAD
                    <flux:field>
                        <flux:label>Email <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="email" type="email" required />
                        <flux:error name="email" />
                    </flux:field>
=======
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" class="w-full" />
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
                </div>
            </div>
        </div>

<<<<<<< HEAD
        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Professional Information</flux:heading>
            <flux:separator variant="subtle" class="mb-6" />
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <flux:field>
                        <flux:label>Office <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="office_id" placeholder="Choose office...">
=======
        <!-- Professional Information Section -->
        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Professional Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ __('Office') }} <span class="text-red-500">*</span>
                        </label>
                        <flux:select wire:model="office_id" placeholder="Choose office..." class="w-full">
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
                            @foreach ($offices as $office)
                                <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
<<<<<<< HEAD
                        <flux:error name="office_id" />
                    </flux:field>
                </div>
                <div class="md:col-span-4">
                    <flux:field>
                        <flux:label>System Role <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="role_id" placeholder="Choose role...">
                            @foreach ($roles as $role)
                                <flux:select.option value="{{ $role->id }}">{{ $role->role }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="role_id" />
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
=======
                    </div>
                </div>
                <div class="md:col-span-4">
                    <flux:input wire:model="position" :label="__('Position')" type="text" required autocomplete="position" class="w-full" />
                </div>
                <div class="md:col-span-4 flex items-end">
                    <div class="w-full flex items-center h-[42px] mt-1.5">
                        <flux:checkbox wire:model="is_head" label="Is head of office" class="w-full" />
                    </div>
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
                </div>
            </div>
        </div>

<<<<<<< HEAD
        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Profile Signature</flux:heading>
            <flux:separator variant="subtle" class="mb-6" />
            
            <div class="flex flex-col sm:flex-row items-start gap-6">
                <div class="flex-shrink-0 relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Current Signature</label>
                    <div class="relative group">
                        @if($signature)
                            <img src="{{ $signature->temporaryUrl() }}" class="w-40 h-20 object-contain border-2 border-gray-200 dark:border-zinc-600 rounded bg-gray-50 dark:bg-zinc-700">
                            <div class="absolute -top-2 -right-2">
                                <flux:button wire:click="$set('signature', null)" icon="x-mark" size="xs" variant="filled" class="rounded-full bg-red-500 hover:bg-red-600 text-white border-none" />
                            </div>
                        @elseif($existingSignature)
                            {{-- Existing DB image --}}
                            <img src="{{ asset('storage/' . $existingSignature) }}" class="w-40 h-20 object-contain border dark:border-zinc-600 rounded">
                        @else
                            <div class="w-40 h-20 bg-gray-50 dark:bg-zinc-700 border-2 border-dashed border-gray-300 dark:border-zinc-600 rounded flex items-center justify-center text-gray-400 dark:text-zinc-400">
                                <span class="text-xs text-gray-400 dark:text-zinc-400">No Signature</span>
=======
        <!-- Profile Picture Section -->
        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Profile Signature</h2>
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <!-- Image Preview -->
                <div class="flex-shrink-0">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Signature</label>
                    <div class="relative">
                        @if($signature)
                            <img src="{{ $signature->temporaryUrl() }}" class="w-40 h-20 object-contain border-2 border-gray-300 rounded">
                            <button wire:click="removePhoto" type="button" class="absolute -top-2 -right-2 bg-red-500 text-white p-1 rounded-full text-xs shadow hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                ✕
                            </button>
                        @else
                            <div class="w-40 h-20 bg-gray-100 border-2 border-dashed border-gray-300 rounded flex items-center justify-center text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
                            </div>
                        @endif
                    </div>
                </div>
                
<<<<<<< HEAD
                <div class="flex-grow">
                    <flux:input type="file" wire:model="signature" label="Upload Signature" description="PNG, JPG (Max: 2MB)" accept="image/png, image/jpeg" />
=======
                <!-- Upload Controls -->
                <div class="flex-grow">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Signature</label>
                    <div class="flex items-center gap-3">
                        <input type="file" wire:model="signature" id="signature" accept="image/*" class="hidden">
                        <label for="signature" class="cursor-pointer bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            {{ $signature ? 'Change File' : 'Select File' }}
                        </label>
                        <span class="text-sm text-gray-500">PNG, JPG up to 2MB</span>
                    </div>
                    @error('signature') 
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
                </div>
            </div>
        </div>

<<<<<<< HEAD
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-zinc-700">
            <flux:button wire:click="cancel" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">{{ $editMode ? 'Update User' : 'Create User' }}</flux:button>
        </div>
    </form>
</div>
=======
        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancel
            </button>
            <button type="submit" wire:click.prevent="saveUser" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Create User
            </button>
        </div>
    </form>
</div>
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
