<?php

namespace App\Livewire\Users;

use App\Http\Controllers\UserController;
use Livewire\Component;

class ListUsers extends Component
{
    public function render()
    {
        return view('livewire.users.list-users', ['users'=>app(UserController::class)->index(true)]);
    }
}
