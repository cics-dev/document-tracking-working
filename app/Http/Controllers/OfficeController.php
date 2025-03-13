<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    // Fetch all offices
    public function index()
    {
        return Office::all();
    }

    // Fetch a single office
    public function show(Office $office)
    {
        return $office;
    }

    // Create a new office
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

    // Update an office
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

    // Delete an office
    public function destroy(Office $office)
    {
        $office->delete();
        return response()->noContent();
    }
}