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
        $this->gender = strtolower($user->profile->gender) ?? '';
        
        // 3. Contact & Work
        $this->email = $user->email ?? '';

        // 4. Signature
        $this->current_signature = $user->signature ?? null;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation()
    {
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
        $user->fill([
            'name' => trim("{$this->given_name} {$this->middle_initial} {$this->family_name} {$this->suffix}"),
            'email' => $this->email,
            'signature'       => $signature_path,
        ]);

        // Construct full name for compatibility
        $user->name = trim("{$this->given_name} {$this->middle_initial} {$this->family_name} {$this->suffix}");

        $user->save();

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
}