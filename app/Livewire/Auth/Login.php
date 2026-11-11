<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\TwoFactorAuthentication;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public string $twoFactorCode = '';

    /**
     * Handle an incoming authentication request.
     */
    public function login(): bool
    {
        Session::forget('two-factor.login');
        $this->validate();

        $this->ensureIsNotRateLimited();

        $credentials = ['email' => $this->email, 'password' => $this->password];
        $challenged = false;

        $authenticated = Auth::attemptWhen($credentials, function (User $user) use ($credentials, &$challenged) {
            if (! $user->require_two_factor_for_login) {
                return true;
            }

            if (config('hashing.rehash_on_login', true)) {
                Auth::getProvider()->rehashPasswordIfRequired($user, $credentials);
            }

            // Keep the current Livewire page's CSRF token valid for the 2FA submission.
            Session::migrate(true);
            Session::put('two-factor.login', [
                'user' => $user->id,
                'password' => hash('sha256', $user->getAuthPassword()),
                'remember' => $this->remember,
                'expires' => now()->addMinutes(5)->timestamp,
            ]);
            $challenged = true;

            return false;
        }, $this->remember);

        if ($challenged) {
            RateLimiter::clear($this->throttleKey());

            return false;
        }

        if (! $authenticated) {
            $this->reset('password');
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        $this->finishLogin();

        // Let the login indicator remain visible until the redirect replaces the page.
        return true;
    }

    public function verifyTwoFactor(TwoFactorAuthentication $twoFactor): void
    {
        abort_if(Auth::check(), 403);

        $code = $this->pull('twoFactorCode');
        $challenge = Session::get('two-factor.login');
        if (! $challenge || now()->timestamp >= $challenge['expires']) {
            $this->cancelTwoFactor();

            throw ValidationException::withMessages(['email' => 'Your login challenge expired. Please log in again.']);
        }

        $twoFactor->throttle($challenge['user'], $code);

        $user = DB::transaction(function () use ($challenge, $twoFactor, $code) {
            $user = User::query()->lockForUpdate()->find($challenge['user']);
            if (! $user?->require_two_factor_for_login
                || ! hash_equals($challenge['password'], hash('sha256', $user->getAuthPassword()))) {
                return null;
            }

            $twoFactor->verify($user, $code);

            return $user;
        });

        $this->cancelTwoFactor();

        if (! $user) {
            throw ValidationException::withMessages(['email' => 'Your login challenge expired. Please log in again.']);
        }

        Auth::login($user, $challenge['remember']);
        $this->finishLogin();
    }

    public function cancelTwoFactor(): void
    {
        Session::forget('two-factor.login');
        $this->reset('twoFactorCode');
        $this->resetValidation('twoFactorCode');
    }

    private function finishLogin(): void
    {
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false));
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
