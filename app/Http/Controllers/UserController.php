<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function index($paginate)
    {
        $users = User::with('profile', 'office');

        return $paginate ? $users->paginate(10) : $users->get();
    }
}
