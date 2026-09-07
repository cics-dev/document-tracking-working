<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index($office_type, $paginate)
    {
        $offices = Office::with('head');
        
        if ($office_type != 'ADMIN') {
            $offices = $offices->where('office_type', 'ADMIN');
        }

        return $paginate ? $offices->paginate(10) : $offices->get();
            
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
<<<<<<< HEAD
=======
            'office_type' => 'required|in:ACAD,ADMIN',
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
            'head_id' => 'nullable|exists:users,id',
        ]);

        return Office::create($request->all());
    }

    public function update(Request $request, Office $office)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:50|unique:offices,abbreviation,' . $office->id,
<<<<<<< HEAD
=======
            'office_type' => 'required|in:ACAD,ADMIN',
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
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