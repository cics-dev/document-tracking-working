<?php

namespace App\Livewire\Users;

use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
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

    protected $rules = [
        'signature' => 'nullable|image|max:2048',
        'family_name' => 'required|string|max:255',
        'given_name' => 'required|string|max:255',
        'middle_name'     => 'sometimes|string|max:255',
        'middle_initial'  => 'required_with:middle_name|string|max:10',
        'honorifics' => 'nullable|string|max:10', // e.g., Mr., Ms., Dr., Mx.
        'suffix'     => 'nullable|string|max:10', // e.g., Jr., Sr., III
        'titles'     => 'nullable|string|max:100', // e.g., PhD, MIT, RN
        'gender'     => 'required|string|max:20',
        'email'      => 'required|email|max:255|unique:users,email',
        'office_id'  => 'required|exists:offices,id',
        'position'   => 'required|string|max:100',
        'is_head'    => 'boolean',
    ];
    
    public function render()
    {
        return view('livewire.users.create-user', ['offices' => app(OfficeController::class)->index('ADMIN', false)]);
    }

    public function saveUser()
    {
        $this->validate($this->rules,['middle_initial.required_with' => 'Required', '*.required' => 'Required']);
        if ($this->signature) {
            $signature_path = $this->signature->store('assets/img', 'public');
        }
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
            'signature'       => $signature_path,
        ]);
        $response = app(UserController::class)->store($data);
        redirect()->route('users.list-users');
    }
}
