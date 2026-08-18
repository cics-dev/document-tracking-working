<?php

namespace App\Livewire\Settings;

use App\Livewire\Actions\Logout;
use App\Services\ArchivalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();
        try {
            app(ArchivalService::class)->archiveUser($user);
        } catch (ValidationException $exception) {
            $this->addError('password', $exception->errors()['archive'][0]);

            return;
        }
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.delete-user-form')->layout('layouts.app');
    }
}
