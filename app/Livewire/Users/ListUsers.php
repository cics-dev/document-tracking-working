<?php

namespace App\Livewire\Users;

use App\Http\Controllers\UserController;
use Livewire\Component;

class ListUsers extends Component
{
    public $users = [];

    public function fetchUsers()
    {
        $response = app(UserController::class)->index();
        $this->users = $response;
    }

    public function mount()
    {
        $this->users = [];
        $this->fetchUsers();
    }

    public function render()
    {
        return view('livewire.users.list-users');
    }
}
