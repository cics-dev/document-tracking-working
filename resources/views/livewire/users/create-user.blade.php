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
                        <flux:select.option value="male">Male</flux:select.option>
                        <flux:select.option value="female">Female</flux:select.option>
                        <flux:select.option value="other">Other</flux:select.option>
                    </flux:select>
                </div>
                <div class="md:col-span-6">
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" class="w-full" />
                </div>
            </div>
        </div>

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
                            @foreach ($offices as $office)
                                <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
                <div class="md:col-span-4">
                    <flux:input wire:model="position" :label="__('Position')" type="text" required autocomplete="position" class="w-full" />
                </div>
                <div class="md:col-span-4 flex items-end">
                    <div class="w-full flex items-center h-[42px] mt-1.5">
                        <flux:checkbox wire:model="is_head" label="Is head of office" class="w-full" />
                    </div>
                </div>
            </div>
        </div>

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
                            </div>
                        @endif
                    </div>
                </div>
                
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
                </div>
            </div>
        </div>

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