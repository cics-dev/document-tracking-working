<?php

namespace App\Livewire\Users;

use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Livewire\Component;use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class CreateUser extends Component
{
    use WithFileUploads;

    public $signature;
    public $family_name = '';
    public $given_name = '';
    public $middle_name = '';
    public $middle_initial = '';
    public $suffix = '';
    public $honorifics = '';
    public $titles = '';
    public $gender = '';
    public $email = '';
    public $office_id = '';
    public $position = '';
    public $is_head = false;
    public $role_id = '';
    public $editMode = false;
    public $userId = null;
    public $existingSignature = null;

    protected function rules()
    {
        return [
            'signature' => 'nullable|image|max:2048',
            'family_name' => 'required|string|max:255',
            'given_name' => 'required|string|max:255',
            'middle_name'     => 'sometimes|string|max:255',
            'middle_initial'  => 'required_with:middle_name|string|max:10',
            'honorifics' => 'nullable|string|max:10', // e.g., Mr., Ms., Dr., Mx.
            'suffix'     => 'nullable|string|max:10', // e.g., Jr., Sr., III
            'titles'     => 'nullable|string|max:100', // e.g., PhD, MIT, RN
            'gender'     => 'required|string|max:20',
            'email'      => 'required|email|max:255|unique:users,email,' . $this->userId,
            'office_id'  => 'required|exists:offices,id',
            'role_id' => 'required|exists:roles,id',
            'position'   => 'required|string|max:100',
            'is_head'    => 'boolean',
        ];
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->editMode = true;
            $user = User::findOrFail($id);

            $this->userId = $user->id;
            $this->family_name = $user->profile->family_name;
            $this->given_name = $user->profile->given_name;
            $this->middle_name = $user->profile->middle_name;
            $this->middle_initial = $user->profile->middle_initial;
            $this->suffix = $user->profile->suffix;
            $this->honorifics = $user->profile->honorifics;
            $this->titles = $user->profile->titles;
            $this->gender = $user->profile->gender;
            $this->email = $user->email;
            $this->office_id = $user->office_id;
            $this->position = $user->position;
            $this->role_id = $user->role_id;
            $this->is_head = $user->office && $user->office->head_id == $user->id;
            $this->existingSignature = $user->signature;
        }
    }
    
    public function cancel()
    {
        return redirect()->route('users.list-users');
    }
    
    public function render()
    {
        return view('livewire.users.create-user', [
            'offices' => app(OfficeController::class)->index('ADMIN', false),
            'roles' => \App\Models\Role::all(),
        ])->layout('layouts.app');
    }

    public function saveUser()
    {
        $this->validate(
            $this->rules(),
            [
                'middle_initial.required_with' => 'Required',
                '*.required' => 'Required',
                'role_id.required' => 'Please assign a role to this user.',
            ]
        );
        $signature_path = $this->signature
        ? $this->signature->store('assets/img', 'public')
        : $this->existingSignature;
        $data = new Request([
            'given_name'      => $this->given_name,
            'middle_name'     => $this->middle_name,
            'middle_initial'  => $this->middle_initial,
            'family_name'     => $this->family_name,
            'suffix'          => $this->suffix,
            'honorifics'      => rtrim($this->honorifics, '.'),
            'titles'          => $this->titles,
            'gender'          => $this->gender,
            'email'           => $this->email,
            'office_id'       => $this->office_id,
            'position'        => $this->position,
            'is_head'         => $this->is_head,
            'role_id'         => $this->role_id,
            'signature'       => $signature_path,
        ]);
        if ($this->editMode) {
            $user = User::find($this->userId);
            app(UserController::class)->update($data, $user);
        } else {
            app(UserController::class)->store($data);
        }
        redirect()->route('users.list-users');
    }
}
