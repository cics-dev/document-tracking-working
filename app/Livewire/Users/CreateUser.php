<?php

namespace App\Livewire\Users;

use App\Http\Controllers\OfficeController;
use App\Models\User;
use App\Services\UserAccountService;
use Livewire\Component;
use Livewire\WithFileUploads;

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
        return app(UserAccountService::class)->rules($this->userId);
    }

    public function mount($id = null)
    {
        abort_unless(auth()->user()?->hasAccess('manage_users'), 403);

        if ($id) {
            $this->editMode = true;
            $user = User::findOrFail($id);

            $this->userId = $user->id;
            foreach (app(UserAccountService::class)->values($user) as $field => $value) {
                $this->{$field} = $value;
            }
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
        $fields = ['given_name', 'middle_name', 'middle_initial', 'family_name', 'suffix', 'honorifics', 'titles', 'gender', 'email', 'office_id', 'position', 'is_head', 'role_id', 'signature'];
        $data = collect($fields)->mapWithKeys(fn ($field) => [$field => $this->{$field}])->all();
        app(UserAccountService::class)->save($data, $this->editMode ? User::findOrFail($this->userId) : null, null, $this->existingSignature);
        redirect()->route('users.list-users');
    }
}
