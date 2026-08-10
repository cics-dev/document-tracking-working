<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

class Profile extends Component
{
    use WithFileUploads;

    // Personal Information
    public $family_name = '';
    public $given_name = '';
    public $middle_name = '';
    public $middle_initial = '';
    public $suffix = '';
    public $honorifics = '';
    public $titles = ''; // "Title" in DB, "titles" in form model
    public $gender = '';
    public $email = '';
    // Kept for compatibility with callers that submit a full name rather than
    // the structured profile fields used by this form.
    public $name = '';

    // Signature
    #[Validate('nullable|image|max:2048')] // 2MB Max
    public $signature; // Holds the new file upload
    public $current_signature; // Holds the existing path from DB

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        // 1. Personal Info
        // We use the null coalescing operator (??) to prevent errors if the field is empty in DB
        $this->family_name = $user->profile->family_name ?? '';
        $this->given_name = $user->profile->given_name ?? '';
        $this->middle_name = $user->profile->middle_name ?? '';
        $this->middle_initial = $user->profile->middle_initial ?? '';
        $this->suffix = $user->profile->suffix ?? '';
        
        // 2. Extra Details
        $this->honorifics = $user->profile->honorifics ?? '';
        $this->titles = $user->profile->titles ?? ''; // Ensure your DB column name matches here
        $this->gender = strtolower($user->profile?->gender) ?? '';
        
        // 3. Contact & Work
        $this->email = $user->email ?? '';
        $this->name = $user->name ?? '';

        // 4. Signature
        $this->current_signature = $user->signature ?? null;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation()
    {
        if (blank($this->given_name) && blank($this->family_name) && filled($this->name)) {
            $nameParts = preg_split('/\s+/', trim($this->name), 2);
            $this->given_name = $nameParts[0] ?? '';
            $this->family_name = $nameParts[1] ?? '';
        }

        // 1. Validation 
        $this->validate([
            'family_name' => 'required',
            'given_name' => 'required',
            'middle_name' => 'nullable',
            'middle_initial' => 'nullable',
            'suffix' => 'nullable',
            'honorifics' => 'nullable',
            'titles' => 'nullable',
            'gender' => 'nullable',
            'signature' => 'nullable|image|max:2048',
            'email' => [
                'required', 
                'string', 
                'lowercase', 
                'email', 
                'max:255', 
                Rule::unique(User::class)->ignore(Auth::id())
            ],
        ], [
            'middle_initial.required_with' => 'Required', 
            '*.required' => 'Required'
        ]);

        $user = Auth::user();
        $signature_path = $user->signature; // Default to existing

        // 2. Handle Signature (Following your specific path logic)
        if ($this->signature) {
            // Optional: Clean up old file
            if ($user->signature && Storage::disk('public')->exists($user->signature)) {
                Storage::disk('public')->delete($user->signature);
            }
            // Store in specific 'assets/img' folder
            $signature_path = $this->signature->store('assets/img', 'public');
        }

        // 3. Update User Data
        $fullName = implode(' ', array_filter([
            trim($this->given_name),
            trim($this->middle_initial),
            trim($this->family_name),
            trim($this->suffix),
        ]));

        $user->fill([
            'name' => $fullName,
            'email' => $this->email,
            'signature'       => $signature_path,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->name = $fullName;

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id], // Search criteria
            [
                'family_name'     => $this->family_name,
                'given_name'      => $this->given_name,
                'middle_name'     => $this->middle_name,
                'middle_initial'  => $this->middle_initial,
                'suffix'          => $this->suffix,
                'honorifics'      => rtrim($this->honorifics, '.'),
                'titles'          => $this->titles,
                'gender'          => $this->gender,
            ]
        );

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

    public function render()
    {
        return view('livewire.settings.profile')->layout('layouts.app');
    }
}
