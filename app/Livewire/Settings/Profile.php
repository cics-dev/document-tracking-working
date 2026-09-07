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
    public $titles = '';
    public $gender = '';
    public $email = '';

    // Signature
    #[Validate('nullable|image|max:2048')]
    public $signature;
    public $current_signature;

    // Profile Picture / Avatar Selection & Upload
    public $selected_preset_avatar = '';
    
    #[Validate('nullable|image|max:3072')]
    public $custom_avatar;
    
    public $captured_avatar = '';
    public $current_avatar = '';

    // Dynamically loaded from folder
    public $preset_avatars = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        // 1. Personal Info
        $this->family_name = $user->profile->family_name ?? '';
        $this->given_name = $user->profile->given_name ?? '';
        $this->middle_name = $user->profile->middle_name ?? '';
        $this->middle_initial = $user->profile->middle_initial ?? '';
        $this->suffix = $user->profile->suffix ?? '';
        
        // 2. Extra Details
        $this->honorifics = $user->profile->honorifics ?? '';
        $this->titles = $user->profile->titles ?? '';
        $this->gender = strtolower($user->profile?->gender) ?? '';
        
        // 3. Contact & Work
        $this->email = $user->email ?? '';

        // 4. Signature & Avatar
        $this->current_signature = $user->signature ?? null;
        $this->current_avatar = $user->avatar ?? null;

        if ($user->avatar && str_starts_with($user->avatar, 'assets/img/avatar/')) {
            $this->selected_preset_avatar = $user->avatar;
        }

        // Load preset avatars dynamically from the folder (only once on mount)
        $this->preset_avatars = $this->getPresetAvatars();
    }

    /**
     * Scan the preset avatars folder and return filenames.
     */
    protected function getPresetAvatars(): array
    {
        $path = public_path('assets/img/avatar');
        if (!is_dir($path)) {
            return [];
        }

        $files = scandir($path);
        $avatars = array_filter($files, function ($file) {
            return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
        });

        sort($avatars);
        return array_values($avatars);
    }

    public function selectPresetAvatar($filename)
    {
        $this->selected_preset_avatar = 'assets/img/avatar/' . $filename;
        $this->custom_avatar = null;
        $this->captured_avatar = '';
    }

    public function removeAvatar()
    {
        $this->selected_preset_avatar = '';
        $this->custom_avatar = null;
        $this->captured_avatar = '';
        $this->current_avatar = null;
    }

    public function setCapturedAvatar($base64Data)
    {
        $this->captured_avatar = $base64Data;
        $this->selected_preset_avatar = '';
        $this->custom_avatar = null;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation()
    {
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
            'custom_avatar' => 'nullable|image|max:3072',
            'email' => [
                'required', 
                'string', 
                'lowercase', 
                'email', 
                'max:255', 
                Rule::unique(User::class)->ignore(Auth::id())
            ],
        ]);

        $user = Auth::user();
        $signature_path = $user->signature;
        $avatar_path = $user->avatar;

        // Handle Signature
        if ($this->signature) {
            if ($user->signature && Storage::disk('public')->exists($user->signature)) {
                Storage::disk('public')->delete($user->signature);
            }
            $signature_path = $this->signature->store('assets/img', 'public');
        }

        // Handle Avatar Priority: 1. Webcam capture, 2. File Upload, 3. Preset Avatar, 4. Removed
        if (!empty($this->captured_avatar)) {
            $image_data = $this->captured_avatar;
            if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $type)) {
                $image_data = substr($image_data, strpos($image_data, ',') + 1);
                $type = strtolower($type[1]);
                $image_data = base64_decode($image_data);

                if ($image_data !== false) {
                    $fileName = 'avatars/cam_' . $user->id . '_' . time() . '.' . $type;
                    Storage::disk('public')->put($fileName, $image_data);
                    $avatar_path = 'storage/' . $fileName;
                }
            }
        } elseif ($this->custom_avatar) {
            $storedPath = $this->custom_avatar->store('avatars', 'public');
            $avatar_path = 'storage/' . $storedPath;
        } elseif (!empty($this->selected_preset_avatar)) {
            $avatar_path = $this->selected_preset_avatar;
        } elseif ($this->current_avatar === null) {
            $avatar_path = null;
        }

        $fullName = trim("{$this->given_name} {$this->middle_initial} {$this->family_name} {$this->suffix}");

        $user->fill([
            'name' => $fullName,
            'email' => $this->email,
            'signature' => $signature_path,
            'avatar' => $avatar_path,
        ]);

        $user->save();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
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

        $this->current_avatar = $user->avatar;
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
