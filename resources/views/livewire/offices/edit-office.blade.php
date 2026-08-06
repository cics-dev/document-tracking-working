@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Office</h1>
            <a href="{{ route('offices.index') }}" class="text-sm text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 transition">
                ← Back to Offices
            </a>
        </div>

        <form action="{{ route('offices.update', $office->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                    <input type="text" name="name" id="name" value="{{ $office->name }}" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                           required>
                </div>

                <div>
                    <label for="abbreviation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Abbreviation</label>
                    <input type="text" name="abbreviation" id="abbreviation" value="{{ $office->abbreviation }}" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                           required>
                </div>

                <div>
                    <label for="office_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Office Type</label>
                    <select name="office_type" id="office_type" 
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                            required>
                        <option value="ACAD" {{ $office->office_type === 'ACAD' ? 'selected' : '' }}>Academic</option>
                        <option value="ADMIN" {{ $office->office_type === 'ADMIN' ? 'selected' : '' }}>Administrative</option>
                    </select>
                </div>

                <div>
                    <label for="head_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Head ID</label>
                    <input type="number" name="head_id" id="head_id" value="{{ $office->head_id }}" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4">
                <button type="button" onclick="window.location='{{ route('offices.index') }}'" 
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Update Office
                </button>
            </div>
        </form>
    </div>
</div>
@endsection