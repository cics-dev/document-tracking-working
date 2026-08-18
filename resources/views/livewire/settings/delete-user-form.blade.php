<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Deactivate account') }}</flux:heading>
        <flux:subheading>{{ __('Deactivate sign-in access while retaining document history') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('Deactivate account') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure you want to deactivate your account?') }}</flux:heading>

                <flux:subheading>
                    {{ __('You will be signed out and unable to sign in. Your account and historical document activity will be retained and an administrator can restore access later. Enter your password to confirm.') }}
                </flux:subheading>
            </div>

            <flux:field><flux:label>{{ __('Password') }} <span class="text-red-500">*</span></flux:label><flux:input wire:model="password" required type="password" /><flux:error name="password" /></flux:field>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Deactivate account') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
