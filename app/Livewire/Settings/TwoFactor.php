<?php

namespace App\Livewire\Settings;

use App\Models\User;
use App\Services\TwoFactorAuthentication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class TwoFactor extends Component
{
    public bool $requireLogin = false;

    public bool $requireSigning = false;

    public string $currentPassword = '';

    public string $twoFactorCode = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->requireLogin = (bool) $user->require_two_factor_for_login;
        $this->requireSigning = (bool) $user->require_two_factor_for_signing;
    }

    public function save(TwoFactorAuthentication $twoFactor): void
    {
        try {
            $this->validate(['currentPassword' => ['required', 'string']]);

            $twoFactor->throttle(Auth::id(), $this->twoFactorCode);

            $recoveryCodes = DB::transaction(function () use ($twoFactor) {
                $user = User::query()->lockForUpdate()->findOrFail(Auth::id());
                if (! Hash::check($this->currentPassword, $user->getAuthPassword())) {
                    throw ValidationException::withMessages(['currentPassword' => __('validation.current_password')]);
                }

                $enable = $this->requireLogin || $this->requireSigning;
                $recoveryCodes = [];

                if ($user->two_factor_secret) {
                    $twoFactor->verify($user, $this->twoFactorCode);
                } elseif ($enable) {
                    $secret = $this->pendingSecret();
                    if (! $secret) {
                        session()->put('two-factor.setup', ['user' => $user->id, 'secret' => $twoFactor->generateSecret()]);

                        return null;
                    }

                    if (trim($this->twoFactorCode) === '') {
                        throw ValidationException::withMessages(['twoFactorCode' => 'Enter the six-digit code from your authenticator app.']);
                    }

                    $user->two_factor_secret = $secret;
                    $twoFactor->verify($user, $this->twoFactorCode, allowRecovery: false);

                    $recoveryCodes = array_map(fn () => Str::random(10).'-'.Str::random(10), range(1, 8));
                    $user->two_factor_recovery_codes = array_map(fn ($code) => hash('sha256', $code), $recoveryCodes);
                }

                if ($this->requireLogin && ! $user->require_two_factor_for_login) {
                    $user->setRememberToken(Str::random(60));
                }

                if (! $enable) {
                    $user->two_factor_secret = null;
                    $user->two_factor_recovery_codes = null;
                    $user->two_factor_last_used_step = null;
                }

                $user->require_two_factor_for_login = $this->requireLogin;
                $user->require_two_factor_for_signing = $this->requireSigning;
                $user->save();

                return $recoveryCodes;
            });
        } finally {
            $this->reset('currentPassword', 'twoFactorCode');
        }

        if ($recoveryCodes !== null) {
            session()->forget('two-factor.setup');
            session()->flash('two-factor.recovery-codes', $recoveryCodes);
            session()->flash('status', 'Two-factor authentication settings saved.');
            Auth::user()->refresh();
        }
    }

    private function pendingSecret(): ?string
    {
        $setup = session('two-factor.setup');

        return ($setup['user'] ?? null) === Auth::id() ? $setup['secret'] : null;
    }

    public function render()
    {
        $user = Auth::user();
        $secret = $user->two_factor_secret ? null : $this->pendingSecret();

        return view('livewire.settings.two-factor', [
            'configured' => filled($user->two_factor_secret),
            'secret' => $secret,
            'qrCode' => $secret ? app(TwoFactorAuthentication::class)->qrCode($user, $secret) : null,
        ])->layout('layouts.app');
    }
}
