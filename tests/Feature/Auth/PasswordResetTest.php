<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('login screen links to the forgot password form', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee(route('password.request'));
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink')
        ->assertHasNoErrors()
        ->assertSee('A password reset link has been sent to your email address.');

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('password reset reports when an account does not exist', function () {
    Notification::fake();

    $response = Livewire::test(ForgotPassword::class)
        ->set('email', 'missing@example.com')
        ->call('sendPasswordResetLink');

    $response->assertHasErrors('email')
        ->assertSee('No account exists for this email address.')
        ->assertDontSee('A password reset link has been sent to your email address.');
    Notification::assertNothingSent();
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
        $response = Livewire::test(ResetPassword::class, ['token' => $notification->token])
            ->set('email', $user->email)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('resetPassword');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
        expect($user->fresh()->remember_token)->not->toBeNull();

        return true;
    });
});
