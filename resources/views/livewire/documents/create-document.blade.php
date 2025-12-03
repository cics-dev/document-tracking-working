<form wire:submit="submitDocument" class="max-w-7xl mx-auto bg-white rounded-xl shadow-[0_0_10px_0_rgba(0,0,0,0.5)] overflow-hidden p-6 relative">
    
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
                <flux:icon icon="x-mark" class="size-4" />
            </button>
        </div>
    </div>

    <div class="hidden" id="complete-template">
        <div class="flex items-center justify-between bg-green-50 border border-green-200 px-3 py-2 rounded-md mb-2">
            <div class="flex items-center min-w-0 flex-1">
                <div class="mr-3 flex-shrink-0 text-green-500">
                    <flux:icon icon="check-circle" class="size-4" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-green-900 truncate file-name">filename.pdf</p>
                    <p class="text-xs text-green-700">Upload complete • <span class="file-size">0 MB</span></p>
                </div>
            </div>
            <button type="button" class="remove-file text-green-700 hover:text-green-900 p-1 ml-2">
                <flux:icon icon="trash" class="size-4" />
            </button>
        </div>
    </div>

    <div class="mb-8">
        <div class="flex items-center justify-between border-b pb-2 mb-4">
            <h2 class="text-xl font-semibold text-gray-900">{{ $revision_document_number ?: 'Document Details' }}</h2>
            
            <div class="flex items-center">
                <flux:switch 
                    wire:model.live="is_manual_document_number" 
                    label="Manual Document No." 
                    class="text-sm"
                />
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <flux:field>
                    <flux:label>Document Number @if($is_manual_document_number)<span class="text-red-500">*</span>@endif</flux:label>
                    
                    @if($is_manual_document_number)
                        <flux:input 
                            wire:model.blur="manual_document_number" 
                            type="text" 
                            placeholder="Enter Reference/Document No."
                            required
                        />
                    @else
                        <div class="relative">
                            <flux:input 
                                type="text" 
                                value="System Generated upon Sending" 
                                disabled 
                                class="bg-gray-100 text-gray-500 italic border-dashed"
                            />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <flux:icon icon="sparkles" class="size-4 text-gray-400" />
                            </div>
                        </div>
                    @endif
                    <flux:error name="document_number" />
                </flux:field>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <flux:field>
                    <flux:label>Type of Document <span class="text-red-500">*</span></flux:label>
                    <flux:select 
                        wire:model="document_type_id" 
                        placeholder="Choose document type..." 
                        wire:change="handleUpdateDocumentType"
                        :disabled="$original_document_number ? true : false"
                    >
                        @foreach ($types as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            @if ($document_type_id)
                @if ($document_type === 'RLM')
                    <div>
                        <flux:field>
                            <flux:label>{{ $document_type === 'RLM' ? 'For' : 'To' }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="document_to_id" placeholder="Choose recipient..." disabled>
                                @foreach ($offices as $office)
                                    <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>
                @elseif ($document_type === 'ECLR' || $document_type === 'Intra')
                    <div>
                        <flux:field>
                            <flux:label>To <span class="text-red-500">*</span></flux:label>
                            <flux:input
                                wire:model.blur="document_to_text"
                                wire:change="updateContentWithTo"
                                placeholder="Enter recipient..."
                                type="text"
                            />
                        </flux:field>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if ($office_type == 'ADMIN' || $document_type == 'IOM' || $document_type == 'SO')
    <div class="mb-8 bg-gray-50/80 p-5 rounded-lg border border-gray-100">
        <flux:label class="mb-2">CF to Offices</flux:label>

        <div class="flex items-center gap-3">
            <div class="flex-1">
                <flux:select wire:model="selected_cf_office" placeholder="Select office...">
                    @foreach ($offices as $office)
                        <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <flux:button wire:click="addCfOffice" icon="plus" variant="filled" class="bg-indigo-600 hover:bg-indigo-700 text-white border-none" />
        </div>

        @if(is_array($cf_offices) && count($cf_offices) > 0)
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($cf_offices as $officeId)
                @php $office = $offices->firstWhere('id', $officeId); @endphp
                @if ($office)
                    <flux:badge size="lg" icon-trailing="x-mark" wire:click="removeCfOffice({{ $officeId }})" class="cursor-pointer bg-indigo-100 text-indigo-900 hover:bg-red-100 hover:text-red-700 transition-colors">
                        {{ $office->name }}
                    </flux:badge>
                @endif
            @endforeach
        </div>
        @endif
    </div>
    @endif

    <div class="mb-8">
        @if ($document_type != 'Intra')
        <div class="mb-4">
            <flux:input wire:model="thru" type="text" autocomplete="thru" label="Thru"/>
        </div>
        @endif

        <div class="mb-4">
            <flux:field>
                <flux:label>Subject <span class="text-red-500">*</span></flux:label>
                <flux:input 
                    wire:model.blur="subject" 
                    wire:change="updateContentWithSubject" 
                    type="text" 
                    required 
                    autocomplete="subject" 
                />
            </flux:field>
        </div>

        <div class="space-y-2">
            <flux:label>Content <span class="text-red-500">*</span></flux:label>
            <div wire:init="loadInitialContent">
                <div wire:ignore class="border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <div id="quill-editor" style="min-height: 200px;" class="bg-white text-gray-900"></div>
                    <input type="hidden" wire:model="content" id="quill-content" />
                </div>
            </div>
            <flux:error name="content" />
        </div>
    </div>

    @if ($document_type != 'IOM')
        @if ($document_type == 'RLM')
        <div class="mb-8 bg-gray-50/80 p-5 rounded-lg border border-gray-100">
            <flux:label class="mb-3">Routing Requirements <span class="text-gray-500 font-normal">- select applicable review offices</span></flux:label>
            <div class="h-4"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <flux:checkbox wire:model="routingRequirements.budget_office" label="Budget Office" value="1" />
                    <flux:checkbox wire:model="routingRequirements.motor_pool" label="Motor Pool" value="2" />
                </div>
                <div class="space-y-3">
                    <flux:checkbox wire:model="routingRequirements.legal_review" label="Legal/Compliance" value="3" />
                    <flux:checkbox wire:model="routingRequirements.igp_review" label="IGP Review" value="4" />
                </div>
            </div>
        </div>

        <div class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-1">
                    <flux:heading size="lg">Signatories</flux:heading>
                    <span class="text-red-500">*</span>
                </div>
                <flux:button wire:click="addSignatory" variant="ghost" size="sm" icon="plus" class="text-indigo-600">Add Signatory</flux:button>
            </div>
            
            <flux:separator variant="subtle" class="mb-4" />

            <flux:error name="signatories" class="mb-2" />

            <div class="space-y-3">
                @foreach ($signatories as $index => $signatory)
                    @php 
                        // specific check for the locked state
                        $isLocked = $signatory['locked'] ?? false; 
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                        <div class="md:col-span-4">
                            <flux:select 
                                wire:model="signatories.{{ $index }}.role" 
                                placeholder="Select Role"
                                :disabled="$isLocked" 
                            >
                                <flux:select.option value="Recommending Approval">Recommending Approval</flux:select.option>
                                <flux:select.option value="Reviewed by">Reviewed by</flux:select.option>
                                <flux:select.option value="Noted by">Noted by</flux:select.option>
                                <flux:select.option value="Approved by">Approved by</flux:select.option>
                                <flux:select.option value="Concurred by">Concurred by</flux:select.option>
                            </flux:select>
                            <flux:error name="signatories.{{ $index }}.role" />
                        </div>

                        <div class="md:col-span-7">
                            <flux:select 
                                wire:model="signatories.{{ $index }}.office_id" 
                                placeholder="Select Signatory"
                                :disabled="$isLocked"
                            >
                                @foreach ($offices as $office)
                                    <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="signatories.{{ $index }}.office_id" />
                        </div>

                        <div class="md:col-span-1 flex justify-end">
                            @if(!$isLocked)
                                <flux:button wire:click="removeSignatory({{ $index }})" icon="trash" variant="subtle" class="text-red-500 hover:text-red-700 hover:bg-red-50 mt-1" />
                            @else
                                <div class="mt-2 text-gray-400" title="Required Signatory">
                                    <flux:icon icon="lock-closed" class="size-5" />
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mb-8">
            <flux:label class="mb-2">Attachments <span class="text-gray-400 text-xs font-normal">(Max 100MB per file)</span></flux:label>

            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-1/3">
                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex flex-col items-center justify-center p-4 text-center">
                            <flux:icon icon="cloud-arrow-up" class="size-6 text-gray-500 mb-2" />
                            <p class="text-xs text-gray-700 mb-1">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-500">
                                PDF, DOCX, XLSX, CSV, IMAGES<br>(MAX. 100MB each)
                            </p>
                        </div>
                        <input 
                            id="dropzone-file" 
                            type="file" 
                            wire:model="attachments" 
                            multiple 
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,image/*,.jpg,.jpeg,.png,.gif"
                            class="hidden"
                        />
                    </label>
                </div>

                <div class="w-full md:w-2/3">
                    @error('attachments.*') 
                        <flux:error name="attachments" />
                    @enderror

                    <div id="file-list" class="space-y-2">
                        @if ($attachments)
                            @foreach ($attachments as $attachment)
                                <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md border border-gray-200">
                                    <div class="flex items-center min-w-0">
                                        <div class="mr-2 flex-shrink-0 text-gray-400">
                                            <flux:icon icon="document" class="size-4" />
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
                                        class="text-red-500 hover:text-red-700 p-1"
                                    >
                                        <flux:icon icon="x-mark" class="size-4" />
                                    </button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col-reverse sm:flex-row justify-between items-center pt-6 border-t border-gray-200 gap-4">
        <flux:button 
            wire:click.prevent="submitDocument('draft')" 
            variant="primary"
            icon="document-text" 
            class="bg-gray-700 hover:bg-gray-800 text-white border-transparent w-full sm:w-auto"
        >
            Save as Draft
        </flux:button>

        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <flux:button 
                wire:click.prevent="previewDocument()" 
                variant="primary"
                icon="eye" 
                class="bg-gray-300 hover:bg-gray-400 text-gray-900 border-transparent w-full sm:w-auto"
            >
                Preview
            </flux:button>
            
            <flux:button 
                type="submit" 
                wire:click.prevent="submitDocument('send')" 
                variant="primary"
                icon="paper-airplane" 
                class="bg-indigo-700 hover:bg-indigo-800 text-white border-transparent w-full sm:w-auto"
            >
                @if ($document_type != 'Intra') Send @else Save @endif
            </flux:button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('dropzone-file');
            const fileList = document.getElementById('file-list');
            const progressTemplate = document.getElementById('progress-template');
            const completeTemplate = document.getElementById('complete-template');
            const uploadSimulations = {};

            function formatFileSize(bytes) {
                const mb = bytes / (1024 * 1024);
                return mb.toFixed(2);
            }

            function calculateUploadTime(fileSize) {
                const baseTime = 1500; 
                const fileSizeMB = fileSize / (1024 * 1024);
                let uploadSpeed;
                if (fileSizeMB < 1) { uploadSpeed = 5; } 
                else if (fileSizeMB < 10) { uploadSpeed = 3; } 
                else { uploadSpeed = 1.5; }
                const uploadTime = (fileSizeMB / uploadSpeed) * 1000;
                return baseTime + uploadTime;
            }

            if(fileInput) {
                fileInput.addEventListener('change', function(e) {
                    handleFiles(e.target.files);
                });

                const dropzone = fileInput.parentElement;
                
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, unhighlight, false);
                });

                function highlight() {
                    dropzone.classList.add('border-green-500', 'bg-green-50');
                }

                function unhighlight() {
                    dropzone.classList.remove('border-green-500', 'bg-green-50');
                }

                dropzone.addEventListener('drop', function(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    handleFiles(files);
                });
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
                    const uploadedMB = formatFileSize(uploadedBytes);
                    mbText.textContent = `${uploadedMB} MB / ${fileSizeMB} MB`;
                    
                    const timeSinceLastUpdate = (currentTime - lastUpdateTime) / 1000;
                    if (timeSinceLastUpdate > 0) {
                        const bytesSinceLastUpdate = uploadedBytes - (progressPercent - 1) * fileSize / 100;
                        const currentSpeed = (bytesSinceLastUpdate / timeSinceLastUpdate) / (1024 * 1024);
                        speedText.textContent = `${Math.max(0.1, currentSpeed).toFixed(2)} MB/s`;
                    }
                    lastUpdateTime = currentTime;

                    if (progress < 0.1) statusText.textContent = 'Preparing...';
                    else if (progress < 0.3) statusText.textContent = 'Uploading...';
                    else if (progress < 0.7) statusText.textContent = 'Processing...';
                    else if (progress < 0.9) statusText.textContent = 'Finalizing...';
                    else statusText.textContent = 'Almost done...';
                }, intervalTime);

                progressElement.dataset.uploadId = uploadId;
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
                setTimeout(() => {
                    completeElement.classList.remove('animate-pulse');
                }, 2000);
            }
        });
    </script>
</form>