<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::all()->load('profile', 'office');
    }

    public function store(Request $request)
    {
        $request->merge([
            'name' => trim(
                $request->given_name . ' ' .
                ($request->middle_initial != '' ? $request->middle_initial . '. ' : '') .
                $request->family_name .
                ($request->suffix != '' ? ' ' . $request->suffix : '')
            ),
            'password' => 'secret'
        ]);
        $user = User::create($request->all());
        $user->profile()->create($request->all());
        if ($request->is_head) {
            $user->office()->update([
                'head_id' => $user->id,
            ]);
        }
        return [$user, $user->profile];
    }
}