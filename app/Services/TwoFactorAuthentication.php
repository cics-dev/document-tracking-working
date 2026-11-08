<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthentication
{
    public function __construct(private Google2FA $authenticator) {}

    public function generateSecret(): string
    {
        return $this->authenticator->generateSecretKey(32);
    }

    public function qrCode(User $user, string $secret): string
    {
        $url = $this->authenticator->getQRCodeUrl(config('app.name'), $user->email, $secret);
        $writer = new Writer(new ImageRenderer(new RendererStyle(192), new SvgImageBackEnd));

        $svg = $writer->writeString($url);

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    public function throttle(int $userId, ?string $code): void
    {
        if (trim($code ?? '') === '') {
            return;
        }

        $key = 'two-factor:'.$userId;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages(['twoFactorCode' => "Too many attempts. Try again in {$seconds} seconds."]);
        }

        // Count attempts before the verification transaction so rollbacks cannot undo them.
        RateLimiter::hit($key);
    }

    /**
     * Verify and consume a code while the caller holds a lock on the user row.
     */
    public function verify(User $user, ?string $code, bool $allowRecovery = true): void
    {
        $code = trim($code ?? '');
        if ($code === '') {
            throw ValidationException::withMessages(['twoFactorCode' => 'Enter your authenticator code or a recovery code.']);
        }

        $step = $user->two_factor_secret && preg_match('/\A[0-9]{6}\z/', $code)
            ? $this->authenticator->verifyKeyNewer($user->two_factor_secret, $code, $user->two_factor_last_used_step ?? 0, 1)
            : false;

        if ($step !== false) {
            $user->forceFill(['two_factor_last_used_step' => $step])->save();
            RateLimiter::clear('two-factor:'.$user->id);

            return;
        }

        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        $index = $allowRecovery ? array_search(hash('sha256', $code), $recoveryCodes, true) : false;

        if ($index !== false) {
            unset($recoveryCodes[$index]);
            $user->forceFill(['two_factor_recovery_codes' => array_values($recoveryCodes)])->save();
            RateLimiter::clear('two-factor:'.$user->id);

            return;
        }

        throw ValidationException::withMessages(['twoFactorCode' => 'The code is invalid or has already been used.']);
    }
}
