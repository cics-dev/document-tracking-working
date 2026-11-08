<?php

use App\Livewire\Auth\Login;
use App\Livewire\Settings\TwoFactor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

test('a confirmed authenticator can protect signing independently of login', function () {
    $user = User::factory()->create();
    $settings = Livewire::actingAs($user)->test(TwoFactor::class)
        ->set('requireSigning', true)
        ->set('currentPassword', 'password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Scan this QR code');

    $secret = session('two-factor.setup.secret');
    expect($user->fresh()->require_two_factor_for_signing)->toBeFalse();

    $settings->set('currentPassword', 'password')
        ->set('twoFactorCode', app(Google2FA::class)->getCurrentOtp($secret))
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Recovery codes');

    $user->refresh();
    expect($user->require_two_factor_for_signing)->toBeTrue()
        ->and($user->require_two_factor_for_login)->toBeFalse()
        ->and($user->getRawOriginal('two_factor_secret'))->not->toBe($secret);

    preg_match('/<li>([a-zA-Z0-9]{10}-[a-zA-Z0-9]{10})<\/li>/', $settings->html(), $matches);
    $recoveryCode = $matches[1];

    Auth::logout();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('dashboard', absolute: false));

    Livewire::test(TwoFactor::class)
        ->set('requireSigning', false)
        ->set('currentPassword', 'password')
        ->set('twoFactorCode', $recoveryCode)
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->require_two_factor_for_signing)->toBeFalse()
        ->and($user->fresh()->two_factor_secret)->toBeNull();
});

test('required login verification keeps the user signed out until a valid code is supplied', function () {
    config(['cache.default' => 'database']);

    $authenticator = app(Google2FA::class);
    $secret = $authenticator->generateSecretKey();
    $user = User::factory()->create();
    $user->forceFill([
        'require_two_factor_for_login' => true,
        'two_factor_secret' => $secret,
    ])->save();

    session()->regenerateToken();
    $csrfToken = session()->token();
    $sessionId = session()->getId();

    $login = Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->set('remember', true)
        ->call('login')
        ->assertHasNoErrors()
        ->assertSee('Verify and log in');

    expect(session()->getId())->not->toBe($sessionId)
        ->and(session()->token())->toBe($csrfToken);

    $this->assertGuest();

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $login->set('twoFactorCode', 'invalid')
            ->call('verifyTwoFactor')
            ->assertHasErrors('twoFactorCode');
    }

    $login->set('twoFactorCode', $authenticator->getCurrentOtp($secret))
        ->call('verifyTwoFactor')
        ->assertHasErrors('twoFactorCode')
        ->assertSee('Too many attempts');

    $this->assertGuest();

    $this->travel(61)->seconds();

    $login->set('twoFactorCode', $authenticator->getCurrentOtp($secret))
        ->call('verifyTwoFactor')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->remember_token)->not->toBeNull();
});
