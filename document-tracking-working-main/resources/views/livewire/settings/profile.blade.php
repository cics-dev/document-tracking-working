<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your personal and professional details')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full">
            
            <div class="mb-8">
                <flux:heading size="lg" class="mb-4">Personal Information</flux:heading>
                <flux:separator variant="subtle" class="mb-6" />
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    <div class="md:col-span-3">
                        <flux:field>
                            <flux:label>Family Name <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="family_name" type="text" required autofocus autocomplete="family-name" />
                            <flux:error name="family_name" />
                        </flux:field>
                    </div>
                    <div class="md:col-span-3">
                        <flux:field>
                            <flux:label>Given Name <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="given_name" type="text" required autocomplete="given-name" />
                            <flux:error name="given_name" />
                        </flux:field>
                    </div>
                    <div class="md:col-span-3">
                        <flux:input wire:model="middle_name" label="Middle Name" type="text" autocomplete="additional-name" />
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
                            <flux:input wire:model="email" type="email" required autocomplete="email" />
                            <flux:error name="email" />
                        </flux:field>

                        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                            <div class="mt-2">
                                <flux:text>
                                    {{ __('Your email address is unverified.') }}
                                    <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                        {{ __('Click here to re-send the verification email.') }}
                                    </flux:link>
                                </flux:text>

                                @if (session('status') === 'verification-link-sent')
                                    <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </flux:text>
                                @endif
                            </div>
                        @endif
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

                            @elseif($current_signature)
                                <img src="{{ asset('storage/' . $current_signature) }}" class="w-40 h-20 object-contain border-2 border-gray-200 rounded bg-gray-50">
                                
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

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end w-full">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        {{-- <livewire:settings.delete-user-form /> --}}
    </x-settings.layout>
</section>