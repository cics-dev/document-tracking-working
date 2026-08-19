<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public $family_name = '';

    public $given_name = '';

    public $middle_name = '';

    public $middle_initial = '';

    public $suffix = '';

    public $honorifics = '';

    public $titles = '';

    public $gender = '';

    public $email = '';

    public $name = '';

    #[Validate('nullable|image|max:2048')]
    public $signature;

    public $current_signature;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->family_name = $user->profile?->family_name ?? '';
        $this->given_name = $user->profile?->given_name ?? '';
        $this->middle_name = $user->profile?->middle_name ?? '';
        $this->middle_initial = $user->profile?->middle_initial ?? '';
        $this->suffix = $user->profile?->suffix ?? '';
        $this->honorifics = $user->profile?->honorifics ?? '';
        $this->titles = $user->profile?->titles ?? '';
        $this->gender = strtolower($user->profile?->gender ?? '');
        $this->email = $user->email;
        $this->name = $user->name;
        $this->current_signature = $user->signature;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        if (blank($this->given_name) && blank($this->family_name) && filled($this->name)) {
            $nameParts = preg_split('/\s+/', trim($this->name), 2);
            $this->given_name = $nameParts[0] ?? '';
            $this->family_name = $nameParts[1] ?? '';
        }

        $this->validate([
            'family_name' => ['required', 'string', 'max:255'],
            'given_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:10'],
            'suffix' => ['nullable', 'string', 'max:10'],
            'honorifics' => ['nullable', 'string', 'max:10'],
            'titles' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'max:20'],
            'signature' => ['nullable', 'image', 'max:2048'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore(Auth::id()),
            ],
        ], [
            '*.required' => 'Required',
        ]);

        $user = Auth::user();
        $oldSignature = $user->signature;
        $signaturePath = $oldSignature;

        if ($this->signature) {
            $signaturePath = $this->signature->store('assets/img', 'public');
        }

        $fullName = implode(' ', array_filter([
            trim($this->given_name),
            trim($this->middle_initial),
            trim($this->family_name),
            trim($this->suffix),
        ]));

        $user->fill([
            'name' => $fullName,
            'email' => $this->email,
            'signature' => $signaturePath,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->name = $fullName;

        $user->profile()->updateOrCreate([], [
            'family_name' => $this->family_name,
            'given_name' => $this->given_name,
            'middle_name' => $this->middle_name,
            'middle_initial' => $this->middle_initial,
            'suffix' => $this->suffix,
            'honorifics' => rtrim($this->honorifics, '.'),
            'titles' => $this->titles,
            'gender' => $this->gender,
        ]);

        if ($oldSignature && $oldSignature !== $signaturePath) {
            Storage::disk('public')->delete($oldSignature);
        }

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function render(): View
    {
        return view('livewire.settings.profile')->layout('layouts.app');
    }
}
