<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Two-factor authentication" subheading="Choose when your account requires a second verification step.">
        @if (session('status'))
            <div class="my-4 rounded bg-green-100 p-3 text-green-800">{{ session('status') }}</div>
        @endif

        @if (session('two-factor.recovery-codes'))
            <div class="my-4 space-y-2 rounded border p-4">
                <flux:heading>Recovery codes</flux:heading>
                <flux:text>Save these codes somewhere safe. Each code can be used once if you lose access to your authenticator app.</flux:text>
                <ul class="grid gap-1 font-mono sm:grid-cols-2">
                    @foreach (session('two-factor.recovery-codes') as $recoveryCode)
                        <li>{{ $recoveryCode }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit="save" class="my-6 space-y-6">
            <flux:checkbox wire:model="requireLogin" label="Require 2FA for login" />
            <flux:checkbox wire:model="requireSigning" label="Require 2FA for signing documents" />

            @if ($secret)
                <div class="space-y-2">
                    <flux:text>Scan this QR code with your authenticator app or enter the setup key manually.</flux:text>
                    <div class="inline-block bg-white p-2">{!! $qrCode !!}</div>
                    <div><code>{{ $secret }}</code></div>
                </div>
            @endif

            <flux:input wire:model="currentPassword" type="password" label="Current password" autocomplete="current-password" required />
            <flux:error name="currentPassword" />

            @if ($configured || $secret)
                <flux:input wire:model="twoFactorCode" :label="$configured ? 'Authenticator or recovery code' : 'Authenticator code'" autocomplete="one-time-code" />
                <flux:error name="twoFactorCode" />
            @endif

            <flux:button type="submit" variant="primary">{{ $secret ? 'Confirm and save' : 'Save' }}</flux:button>
        </form>
    </x-settings.layout>
</section>
