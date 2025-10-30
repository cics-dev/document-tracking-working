<form wire:submit.prevent="submitDocument" class="max-w-7xl mx-auto bg-white rounded-xl shadow-[0_0_10px_0_rgba(0,0,0,0.5)] overflow-hidden p-6">
    <style>
        @keyframes glow {
          0% { box-shadow: 0 0 5px rgba(34, 197, 94, 0.4); }
          50% { box-shadow: 0 0 15px rgba(34, 197, 94, 0.8); }
          100% { box-shadow: 0 0 5px rgba(34, 197, 94, 0.4); }
        }
        .animate-glow { animation: glow 2s ease-in-out infinite; }
        
        /* Green gradient for upload progress */
        .upload-progress-gradient {
            background: linear-gradient(90deg, #4ade80, #22c55e, #16a34a);
            background-size: 200% 100%;
            animation: gradient-shift 3s ease infinite;
        }
        
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>

    <!-- Hidden templates for upload progress -->
    <div class="hidden" id="progress-template">
        <div class="flex items-center justify-between bg-green-50 border border-green-200 px-3 py-2 rounded-md mb-2 animate-glow">
            <div class="flex items-center min-w-0 flex-1">
                <div class="mr-3 flex-shrink-0">
                    <svg class="w-4 h-4 text-green-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex justify-between items-center mb-1">
                        <p class="text-xs font-medium text-green-900 truncate file-name max-w-[120px]">filename.pdf</p>
                        <p class="text-xs text-green-700 upload-mb">0 MB / 0 MB</p>
                    </div>
                    <div class="w-full bg-green-200 rounded-full h-1.5">
                        <div class="upload-progress-gradient h-1.5 rounded-full upload-progress" style="width: 0%"></div>
                    </div>
                    <div class="flex justify-between items-center mt-1">
                        <p class="text-xs text-green-700">
                            <span class="upload-status">Preparing...</span>
                        </p>
                        <p class="text-xs text-green-700 upload-speed">0 MB/s</p>
                    </div>
                </div>
            </div>
            <button type="button" class="cancel-upload text-green-700 hover:text-green-900 p-1 ml-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>

    <div class="hidden" id="complete-template">
        <div class="flex items-center justify-between bg-green-50 border border-green-200 px-3 py-2 rounded-md mb-2">
            <div class="flex items-center min-w-0 flex-1">
                <div class="mr-3 flex-shrink-0 text-green-500">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-green-900 truncate file-name">filename.pdf</p>
                    <p class="text-xs text-green-700">Upload complete • <span class="file-size">0 MB</span></p>
                </div>
            </div>
            <button type="button" class="remove-file text-green-700 hover:text-green-900 p-1 ml-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Document Type Section -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">{{ $revision_document_number?$revision_document_number:'Document Details' }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Document Type -->
            <div class="space-y-2">
                <label for="type-of-document" class="block text-sm font-medium text-gray-900">
                    {{ __('Type of Document') }} <span class="text-red-600">*</span>
                </label>
                @php $isDisabled = $original_document_number?true:false @endphp
                <flux:select 
                    wire:model="document_type_id" 
                    placeholder="Choose document type..." 
                    wire:change="handleUpdateDocumentType"
                    class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-gray-900"
                    :disabled="$isDisabled"
                >
                    @foreach ($types as $type)
                        <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Recipient Field (Conditional) -->
            @if ($document_type_id)
                @if ($document_type === 'RLM')
                    <div class="space-y-2">
                        <label for="To" class="block text-sm font-medium text-gray-900">
                            {{ __($document_type === 'RLM'?'For':'To') }} <span class="text-red-600">*</span>
                        </label>
                        <flux:select 
                            wire:model="document_to_id" 
                            placeholder="Choose recipient..."
                            class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-gray-900"
                            disabled
                        >
                            @foreach ($offices as $office)
                                <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @elseif ($document_type === 'ECLR' || $document_type === 'Intra')
                    <div class="space-y-2">
                        <label for="To" class="block text-sm font-medium text-gray-900">
                            {{ __('To') }} <span class="text-red-600">*</span>
                        </label>
                        <flux:input
                            wire:model.blur="document_to_text"
                            wire:change="updateContentWithTo"
                            placeholder="Enter recipient..."
                            type="text"
                            class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-gray-900"
                        />
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- CF Offices Section (Admin Only) -->
    @if ($office_type == 'ADMIN' || $document_type == 'IOM' || $document_type == 'SO')
    <div class="mb-8 bg-gray-50 p-4 rounded-lg">
        <label class="block text-sm font-medium text-gray-900 mb-2">
            {{ __('CF to Offices') }}
        </label>

        <div class="flex items-center gap-3">
            <div class="flex-1">
                <flux:select 
                    wire:model="selected_cf_office" 
                    placeholder="Select office..."
                    class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-gray-900"
                >
                    @foreach ($offices as $office)
                        <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <button 
                type="button" 
                wire:click="addCfOffice" 
                class="flex-shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white p-2 rounded-full shadow-sm transition-colors duration-200"
                title="Add Office"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <!-- CF Office Chips -->
       @if(is_array($cf_offices) && count($cf_offices) > 0)
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($cf_offices as $officeId)
                @php
                    $office = $offices->firstWhere('id', $officeId);
                @endphp
                @if ($office)
                    <div class="inline-flex items-center bg-indigo-100 text-indigo-900 px-3 py-1 rounded-full text-xs font-medium">
                        {{ $office->name }}
                        <button 
                            type="button" 
                            wire:click="removeCfOffice({{ $officeId }})" 
                            class="ml-2 text-indigo-700 hover:text-indigo-900"
                            title="Remove"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                @endif
            @endforeach
        </div>
        @endif
    </div>
    @endif

    <!-- Subject and Content Section -->
    <div class="mb-8">
        @if ($document_type != 'Intra')
        <div class="mb-4">
            <flux:input
                wire:model="thru"
                :label="__('Thru')"
                type="text"
                required
                autocomplete="thru"
                class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-gray-900"
            />
        </div>
        @endif

        <div class="mb-4">
            <flux:input
                wire:model.blur="subject"
                wire:change="updateContentWithSubject"
                :label="__('Subject')"
                type="text"
                required
                autocomplete="subject"
                class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-gray-900"
            />
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-900">
                {{ __('Content') }} <span class="text-red-600">*</span>
            </label>
            <div wire:ignore class="border border-gray-300 rounded-md overflow-hidden">
                <div id="quill-editor" style="min-height: 200px;" class="text-gray-900"></div>
                <input type="hidden" wire:model="content" id="quill-content" />
            </div>
        </div>
    </div>

    <!-- Routing Requirements Section -->
    @if ($document_type != 'IOM')
        @if ($document_type == 'RLM')
        <div class="mb-8 bg-gray-50 p-4 rounded-lg">
            <label class="block text-sm font-medium text-gray-900 mb-3">
                {{ __('Routing Requirements') }} - select applicable review offices
            </label>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Column 1 -->
                <div class="space-y-3">
                    <!-- Budget Office -->
                    <div class="flex items-center">
                        <input 
                            wire:model="routingRequirements.budget_office" 
                            id="budget_office" 
                            type="checkbox" 
                            value="1"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-600 border-gray-300 rounded"
                        >
                        <label for="budget_office" class="ml-2 block text-sm text-gray-900">
                            Budget Office
                        </label>
                    </div>
                    
                    <!-- Motor Pool -->
                    <div class="flex items-center">
                        <input 
                            wire:model="routingRequirements.motor_pool" 
                            id="motor_pool" 
                            type="checkbox" 
                            value="2"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-600 border-gray-300 rounded"
                        >
                        <label for="motor_pool" class="ml-2 block text-sm text-gray-900">
                            Motor Pool
                        </label>
                    </div>
                </div>
                
                <!-- Column 2 -->
                <div class="space-y-3">
                    <!-- Legal Review -->
                    <div class="flex items-center">
                        <input 
                            wire:model="routingRequirements.legal_review" 
                            id="legal_review" 
                            type="checkbox" 
                            value="3"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-600 border-gray-300 rounded"
                        >
                        <label for="legal_review" class="ml-2 block text-sm text-gray-900">
                            Legal/Compliance
                        </label>
                    </div>
                    
                    <!-- IGP Review -->
                    <div class="flex items-center">
                        <input 
                            wire:model="routingRequirements.igp_review" 
                            id="igp_review" 
                            type="checkbox" 
                            value="4"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-600 border-gray-300 rounded"
                        >
                        <label for="igp_review" class="ml-2 block text-sm text-gray-900">
                            IGP Review
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signatories Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-900">
                    {{ __('Signatories') }}
                </label>
                <button 
                    type="button" 
                    wire:click="addSignatory" 
                    class="text-sm text-indigo-700 hover:text-indigo-900 flex items-center font-medium"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add Signatory
                </button>
            </div>

            <div class="space-y-3">
                @foreach ($signatories as $index => $signatory)
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                        <div class="md:col-span-4">
                            <select 
                                wire:model="signatories.{{ $index }}.role" 
                                class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-sm text-gray-900"
                            >
                                <option value="">Select Role</option>
                                <option value="Recommending Approval">Recommending Approval</option>
                                <option value="Reviewed by">Reviewed by</option>
                                <option value="Noted by">Noted by</option>
                                <option value="Approved by">Approved by</option>
                                <option value="Concurred by">Concurred by</option>
                            </select>
                        </div>

                        <div class="md:col-span-7">
                            <select 
                                wire:model="signatories.{{ $index }}.office_id" 
                                class="w-full border-gray-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm text-sm text-gray-900"
                            >
                                <option value="">Select Signatory</option>
                                @foreach ($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-1 flex justify-end">
                            <button 
                                type="button" 
                                wire:click="removeSignatory({{ $index }})" 
                                class="text-red-600 hover:text-red-800 p-1"
                                title="Remove"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    <!-- Attachments Section -->
    <div class="mb-8">
        <label for="attachments" class="block text-sm font-medium text-gray-900 mb-2">
            {{ __('Attachments') }} <span class="text-gray-500 text-xs">(Max 100MB per file)</span>
        </label>

        <div class="flex flex-col md:flex-row gap-4">
            <!-- Left Side - Upload Area -->
            <div class="w-full md:w-1/3">
                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex flex-col items-center justify-center p-4 text-center">
                        <svg class="w-6 h-6 mb-2 text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                        </svg>
                        <p class="text-xs text-gray-700 mb-1">Click to upload or drag and drop</p>
                        <p class="text-xs text-gray-500">
                            PDF, IMAGES<br>(MAX. 100MB each)
                        </p>
                    </div>
                    <input 
                        id="dropzone-file" 
                        type="file" 
                        wire:model="attachments" 
                        multiple 
                        accept=".pdf,image/*,.jpg,.jpeg,.png"
                        class="hidden"
                    />
                </label>
            </div>

            <!-- Right Side - File List -->
            <div class="w-full md:w-2/3">
                @error('attachments.*') 
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                @enderror

                <!-- File list container for JavaScript upload simulation -->
                <div id="file-list" class="space-y-2">
                    @if ($attachments)
                        @foreach ($attachments as $attachment)
                            <div class="flex items-center justify-between bg-gray-100 px-3 py-2 rounded-md">
                                <div class="flex items-center min-w-0">
                                    @php
                                        $icon = match(true) {
                                            str_contains($attachment->getClientMimeType(), 'pdf') => 'pdf',
                                            str_contains($attachment->getClientMimeType(), 'word') => 'word',
                                            str_contains($attachment->getClientMimeType(), 'excel') => 'excel',
                                            str_contains($attachment->getClientMimeType(), 'csv') => 'csv',
                                            str_contains($attachment->getClientMimeType(), 'image') => 'image',
                                            default => 'file'
                                        };
                                    @endphp
                                    
                                    <div class="mr-2 flex-shrink-0">
                                        @if($icon === 'pdf')
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                        @elseif($icon === 'word')
                                        <svg class="w-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                        @elseif($icon === 'excel')
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                        @elseif($icon === 'csv')
                                        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                        @elseif($icon === 'image')
                                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        @else
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        @endif
                                    </div>
                                    
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-900 truncate">
                                            {{ $attachment->getClientOriginalName() }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ round($attachment->getSize() / 1024 / 1024, 2) }} MB • 
                                            {{ strtoupper($attachment->getClientOriginalExtension()) }}
                                        </p>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    wire:click="removeAttachment('{{ $attachment->getClientOriginalName() }}')" 
                                    class="text-red-600 hover:text-red-800 p-1"
                                    title="Remove"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Form Actions -->
    <div class="flex flex-col-reverse sm:flex-row justify-between items-center pt-4 border-t border-gray-200">
        <button 
            type="button" 
            wire:click.prevent="submitDocument('draft')" 
            class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-600 transition ease-in-out duration-150"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            Save as Draft
        </button>

        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <button 
                type="button" 
                wire:click.prevent="previewDocument()" 
                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-medium text-xs text-gray-900 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Preview
            </button>
            <button 
                type="button" 
                wire:click.prevent="submitDocument('send')" 
                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-700 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 transition ease-in-out duration-150"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                @if ($document_type != 'Intra') Send @else Save @endif
            </button>
        </div>
    </div>

    <script>
        function initFileUploadSimulation() {
            const fileInput = document.getElementById('dropzone-file');
            const fileList = document.getElementById('file-list');
            const progressTemplate = document.getElementById('progress-template');
            const completeTemplate = document.getElementById('complete-template');

            if (!fileInput || !fileList || !progressTemplate || !completeTemplate) return; // Avoid re-initializing if elements not found

            const uploadSimulations = {};

            function formatFileSize(bytes) {
                const mb = bytes / (1024 * 1024);
                return mb.toFixed(2);
            }

            function calculateUploadTime(fileSize) {
                const baseTime = 1500;
                const fileSizeMB = fileSize / (1024 * 1024);
                let uploadSpeed;
                if (fileSizeMB < 1) uploadSpeed = 5;
                else if (fileSizeMB < 10) uploadSpeed = 3;
                else uploadSpeed = 1.5;
                return baseTime + (fileSizeMB / uploadSpeed) * 1000;
            }

            function handleFiles(files) {
                if (!files || files.length === 0) return;
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (file.size > 100 * 1024 * 1024) {
                        alert(`File "${file.name}" is too large. Maximum size is 100MB.`);
                        continue;
                    }
                    const progressElement = createUploadProgress(file);
                    simulateUpload(file, progressElement);
                }
            }

            function createUploadProgress(file) {
                const progressElement = progressTemplate.cloneNode(true);
                progressElement.classList.remove('hidden');
                progressElement.id = `upload-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
                progressElement.querySelector('.file-name').textContent = file.name;
                const fileSizeMB = formatFileSize(file.size);
                progressElement.querySelector('.upload-mb').textContent = `0.00 MB / ${fileSizeMB} MB`;

                progressElement.querySelector('.cancel-upload').addEventListener('click', function() {
                    cancelUpload(progressElement);
                });

                fileList.appendChild(progressElement);
                progressElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                return progressElement;
            }

            function simulateUpload(file, progressElement) {
                const progressBar = progressElement.querySelector('.upload-progress');
                const mbText = progressElement.querySelector('.upload-mb');
                const statusText = progressElement.querySelector('.upload-status');
                const speedText = progressElement.querySelector('.upload-speed');
                const uploadId = progressElement.id;
                const fileSize = file.size;
                const fileSizeMB = formatFileSize(fileSize);

                let uploadedBytes = 0;
                const totalTime = calculateUploadTime(fileSize);
                const intervalTime = 100;
                const steps = totalTime / intervalTime;
                const bytesPerStep = fileSize / steps;
                const startTime = Date.now();
                let lastUpdateTime = startTime;

                uploadSimulations[uploadId] = setInterval(() => {
                    const currentTime = Date.now();
                    const elapsed = currentTime - startTime;
                    let progress = Math.min(elapsed / totalTime, 1);
                    const randomness = 0.9 + (Math.random() * 0.2);
                    progress = Math.min(progress * randomness, 1);
                    uploadedBytes = progress * fileSize;

                    if (progress >= 1) {
                        uploadedBytes = fileSize;
                        clearInterval(uploadSimulations[uploadId]);
                        completeUpload(file, progressElement);
                        return;
                    }

                    const progressPercent = progress * 100;
                    progressBar.style.width = `${progressPercent}%`;
                    mbText.textContent = `${formatFileSize(uploadedBytes)} MB / ${fileSizeMB} MB`;

                    const timeSinceLastUpdate = (currentTime - lastUpdateTime) / 1000;
                    if (timeSinceLastUpdate > 0) {
                        const currentSpeed = (bytesPerStep / timeSinceLastUpdate) / (1024 * 1024);
                        speedText.textContent = `${Math.max(0.1, currentSpeed).toFixed(2)} MB/s`;
                    }
                    lastUpdateTime = currentTime;

                    if (progress < 0.1) statusText.textContent = 'Preparing...';
                    else if (progress < 0.3) statusText.textContent = 'Uploading...';
                    else if (progress < 0.7) statusText.textContent = 'Processing...';
                    else if (progress < 0.9) statusText.textContent = 'Finalizing...';
                    else statusText.textContent = 'Almost done...';
                }, intervalTime);
            }

            function cancelUpload(progressElement) {
                const uploadId = progressElement.dataset.uploadId;
                if (uploadSimulations[uploadId]) {
                    clearInterval(uploadSimulations[uploadId]);
                    delete uploadSimulations[uploadId];
                }
                progressElement.remove();
            }

            function completeUpload(file, progressElement) {
                const completeElement = completeTemplate.cloneNode(true);
                completeElement.classList.remove('hidden');
                completeElement.querySelector('.file-name').textContent = file.name;
                completeElement.querySelector('.file-size').textContent = `${formatFileSize(file.size)} MB`;
                completeElement.querySelector('.remove-file').addEventListener('click', function() {
                    completeElement.remove();
                });
                progressElement.replaceWith(completeElement);
                completeElement.classList.add('animate-pulse');
                setTimeout(() => completeElement.classList.remove('animate-pulse'), 2000);
            }

            // Drag & Drop
            const dropzone = fileInput.parentElement;
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => dropzone.addEventListener(e, preventDefaults, false));
            ['dragenter', 'dragover'].forEach(e => dropzone.addEventListener(e, () => highlight(dropzone), false));
            ['dragleave', 'drop'].forEach(e => dropzone.addEventListener(e, () => unhighlight(dropzone), false));
            dropzone.addEventListener('drop', e => handleFiles(e.dataTransfer.files));

            fileInput.addEventListener('change', e => {
                handleFiles(e.target.files);
                e.target.value = '';
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            function highlight(el) {
                el.classList.add('border-green-500', 'bg-green-50');
            }
            function unhighlight(el) {
                el.classList.remove('border-green-500', 'bg-green-50');
            }
        }

        // Run on page load and every Livewire DOM change
        document.addEventListener('DOMContentLoaded', initFileUploadSimulation);
        document.addEventListener('livewire:load', initFileUploadSimulation);
        document.addEventListener('livewire:navigated', initFileUploadSimulation);
    </script>
</form>