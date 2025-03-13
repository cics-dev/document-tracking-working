@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Edit Office</h1>
    <form action="{{ route('offices.update', $office->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" id="name" value="{{ $office->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
        </div>
        <div class="mb-4">
            <label for="abbreviation" class="block text-sm font-medium text-gray-700">Abbreviation</label>
            <input type="text" name="abbreviation" id="abbreviation" value="{{ $office->abbreviation }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
        </div>
        <div class="mb-4">
            <label for="office_type" class="block text-sm font-medium text-gray-700">Office Type</label>
            <select name="office_type" id="office_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                <option value="ACAD" {{ $office->office_type === 'ACAD' ? 'selected' : '' }}>Academic</option>
                <option value="ADMIN" {{ $office->office_type === 'ADMIN' ? 'selected' : '' }}>Administrative</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="head_id" class="block text-sm font-medium text-gray-700">Head ID</label>
            <input type="number" name="head_id" id="head_id" value="{{ $office->head_id }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
@endsection