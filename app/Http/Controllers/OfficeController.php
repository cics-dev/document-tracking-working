<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{
    public function index(string $officeType, bool $paginate)
    {
        $offices = Office::with('head')
            ->when($officeType !== 'ADMIN', fn ($query) => $query->where('office_type', 'ADMIN'));

        return $paginate ? $offices->paginate() : $offices->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:50|unique:offices,abbreviation',
            'office_type' => 'nullable|string|max:50',
            'office_logo' => 'nullable|string|max:255',
            'head_id' => 'nullable|exists:users,id',
            'acting_head_id' => 'nullable|exists:users,id',
        ]);

        return Office::create($validated);
    }

    public function update(Request $request, Office $office)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => ['required', 'string', 'max:50', Rule::unique('offices')->ignore($office)],
            'office_type' => 'nullable|string|max:50',
            'office_logo' => 'nullable|string|max:255',
            'head_id' => 'nullable|exists:users,id',
            'acting_head_id' => 'nullable|exists:users,id',
        ]);

        $office->update($validated);

        return $office;
    }
}
