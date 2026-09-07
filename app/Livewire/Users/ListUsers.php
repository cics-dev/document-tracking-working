<?php

namespace App\Livewire\Users;

use App\Http\Controllers\UserController;
use Livewire\Component;
<<<<<<< HEAD
use App\Models\User;
=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045

class ListUsers extends Component
{
    public function render()
    {
<<<<<<< HEAD
        return view('livewire.users.list-users', ['users'=>app(UserController::class)->index(true)])->layout('layouts.app');
    }

    public function editUser($id)
    {
        $user = User::with('profile', 'office')->findOrFail($id);

        $this->userId = $user->id;
        $this->editMode = true;

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
        $this->position = $user->profile->position;
        $this->role_id = $user->role_id;

        $this->is_head = $user->office && $user->office->head_id == $user->id;

        return redirect()->route('users.edit-user', $id);
=======
        return view('livewire.users.list-users', ['users'=>app(UserController::class)->index(true)]);
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    }
}
