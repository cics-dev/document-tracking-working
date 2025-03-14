<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index($mode)
    {
        if ($mode === 'sent') {
            return Document::where('office_id', Auth::user()->office->id)->get();
        }
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
        $user = Document::create($request->all());
        $user->profile()->create($request->all());
        if ($request->is_head) {
            $user->office()->update([
                'head_id' => $user->id,
            ]);
        }
        return [$user, $user->profile];
    }
}
