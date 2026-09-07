<div class="flex items-start max-md:flex-col">
    <div class="mr-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
<<<<<<< HEAD
            <flux:navlist.item :href="route('settings.profile')">{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')">{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')">{{ __('Appearance') }}</flux:navlist.item>
=======
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

<<<<<<< HEAD
        <div class="mt-5 w-full pr-12">
=======
        <div class="mt-5 w-full max-w-lg">
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
            {{ $slot }}
        </div>
    </div>
</div>
