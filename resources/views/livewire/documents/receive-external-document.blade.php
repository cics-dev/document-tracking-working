<form wire:submit.prevent="submitDocument" class="max-w-7xl mx-auto bg-white rounded-xl shadow-[0_0_10px_0_rgba(0,0,0,0.5)] overflow-hidden p-6">
    <!-- Document Type Section -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Document Details</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label for="From" class="block text-sm font-medium text-gray-900">
                    {{ __('From') }} <span class="text-red-600">*</span>
                </label>
                <flux:input
                    wire:model.blur="document_from"
                    placeholder="Enter sender..."
                    type="text"
                    class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-gray-900"
                />
            </div>
            <div class="space-y-2">
                <label for="To" class="block text-sm font-medium text-gray-900">
                    To <span class="text-red-600">*</span>
                </label>
                <flux:select 
                    wire:model="document_to_id" 
                    placeholder="Choose recipient..."
                    class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-gray-900"
                >
                    @foreach ($offices as $office)
                        <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="mb-4 mt-2">
            <flux:input
                wire:model.blur="subject"
                :label="__('Subject')"
                type="text"
                required
                autocomplete="subject"
                class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-gray-900"
            />
        </div>

        <div class="mb-8 mt-2">
            <label for="attachment" class="block text-sm font-medium text-gray-900 mb-2">
                {{ __('Attachment') }} <span class="text-gray-500 text-xs">(Max 100MB)</span>
            </label>

            @if (!$attachment)
                <label for="attachment" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex flex-col items-center justify-center p-4 text-center">
                        <svg class="w-6 h-6 mb-2 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                        </svg>
                        <p class="text-xs text-gray-700 mb-1">Click to upload or drag and drop</p>
                        <p class="text-xs text-gray-500">PDF, IMAGES<br>(MAX. 100MB)</p>
                    </div>
                    <input 
                        id="attachment" 
                        type="file" 
                        wire:model="attachment" 
                        accept=".pdf,image/*,.jpg,.jpeg,.png"
                        class="hidden"
                    />
                </label>
            @else
                <div class="flex items-center justify-between bg-gray-100 px-3 py-2 rounded-md">
                    <div class="flex items-center min-w-0">
                        @php
                            $mime = $attachment->getClientMimeType();
                            $icon = str_contains($mime, 'pdf') ? 'pdf' :
                                    (str_contains($mime, 'image') ? 'image' : 'file');
                        @endphp

                        @if ($icon === 'pdf')
                            <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                        @elseif ($icon === 'image')
                            <svg class="w-4 h-4 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3h12a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V4a1 1 0 011-1z"/></svg>
                        @else
                            <svg class="w-4 h-4 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3h12a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V4a1 1 0 011-1z"/></svg>
                        @endif

                        <div class="min-w-0">
                            <p class="text-xs font-medium text-gray-900 truncate">
                                {{ $attachment->getClientOriginalName() }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ round($attachment->getSize() / 1024 / 1024, 2) }} MB
                            </p>
                        </div>
                    </div>

                    <button 
                        type="button" 
                        wire:click="removeAttachment" 
                        class="text-red-600 hover:text-red-800 p-1"
                        title="Remove"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 6h8v2H6V6zm0 4h8v2H6v-2zM4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            @endif

            @error('attachment')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end items-center pt-4 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto justify-end">
                <button 
                    type="button" 
                    wire:click.prevent="submitDocument()" 
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-700 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
