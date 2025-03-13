@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Create New Office</h1>
    <form action="{{ route('offices.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
        </div>
        <div class="mb-4">
            <label for="abbreviation" class="block text-sm font-medium text-gray-700">Abbreviation</label>
            <input type="text" name="abbreviation" id="abbreviation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
        </div>
        <div class="mb-4">
            <label for="office_type" class="block text-sm font-medium text-gray-700">Office Type</label>
            <select name="office_type" id="office_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                <option value="ACAD">Academic</option>
                <option value="ADMIN">Administrative</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="head_id" class="block text-sm font-medium text-gray-700">Head ID</label>
            <input type="number" name="head_id" id="head_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
    </form>
</div>
@endsection