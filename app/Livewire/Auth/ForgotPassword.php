<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ForgotPassword extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        session()->forget('status');
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink($this->only('email'));
        if ($status !== Password::RESET_LINK_SENT) {
            $this->addError('email', $status === Password::INVALID_USER
                ? __('No account exists for this email address.')
                : __($status));
            return;
        }

        session()->flash('status', __('A password reset link has been sent to your email address.'));
    }
}
