<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index($office_type)
    {
        if ($office_type != 'ADMIN') return Office::where('office_type', 'ADMIN')->with('head')->get();
        return Office::all()->load('head');
    }

    public function show(Office $office)
    {
        return $office;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:50|unique:offices,abbreviation',
            'office_type' => 'required|in:ACAD,ADMIN',
            'head_id' => 'nullable|exists:users,id',
        ]);

        return Office::create($request->all());
    }

    public function update(Request $request, Office $office)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:50|unique:offices,abbreviation,' . $office->id,
            'office_type' => 'required|in:ACAD,ADMIN',
            'head_id' => 'nullable|exists:users,id',
        ]);

        $office->update($request->all());
        return $office;
    }

    public function destroy(Office $office)
    {
        $office->delete();
        return response()->noContent();
    }
}