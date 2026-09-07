<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index($paginate)
    {
        $users = User::with('profile', 'office');

        return $paginate ? $users->paginate(10) : $users->get();
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
            'password' => 'password'
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

    public function update(Request $request, User $user)
    {
        $request->merge([
            'name' => trim(
                $request->given_name . ' ' .
                ($request->middle_initial != '' ? $request->middle_initial . '. ' : '') .
                $request->family_name .
                ($request->suffix != '' ? ' ' . $request->suffix : '')
            ),
        ]);

        // ------------------------
        // USER TABLE FIELDS ONLY
        // ------------------------
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'office_id' => $request->office_id,
            'role_id' => $request->role_id,
            'position' => $request->position,
            'signature' => $request->signature,
        ]);

        // ------------------------
        // PROFILE FIELDS ONLY
        // ------------------------
        $user->profile()->update([
            'given_name' => $request->given_name,
            'middle_name' => $request->middle_name,
            'middle_initial' => $request->middle_initial,
            'family_name' => $request->family_name,
            'suffix' => $request->suffix,
            'honorifics' => $request->honorifics,
            'titles' => $request->titles,
            'gender' => $request->gender,
        ]);

        // ------------------------
        // OFFICE HEAD LOGIC
        // ------------------------
        if ($request->is_head) {
            $user->office()->update([
                'head_id' => $user->id,
            ]);
        }

        return $user;
    }
}