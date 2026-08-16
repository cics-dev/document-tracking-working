<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Confirm password')"
        :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
    />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
        <!-- Password -->
        <flux:field><flux:label>{{ __('Password') }} <span class="text-red-500">*</span></flux:label><flux:input
            wire:model="password"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
        /><flux:error name="password" /></flux:field>

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Confirm') }}</flux:button>
    </form>
</div>
