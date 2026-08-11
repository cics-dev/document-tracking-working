<div class="max-w-7xl mx-auto bg-white rounded-xl shadow-[0_0_10px_0_rgba(0,0,0,0.5)] overflow-hidden p-6 relative">
    
    <style>
        @keyframes glow {
          0% { box-shadow: 0 0 5px rgba(34, 197, 94, 0.4); }
          50% { box-shadow: 0 0 15px rgba(34, 197, 94, 0.8); }
          100% { box-shadow: 0 0 5px rgba(34, 197, 94, 0.4); }
        }
        .animate-glow { animation: glow 2s ease-in-out infinite; }
        
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
        <flux:heading size="xl" class="mb-4 border-b border-gray-200 pb-2">Document Details</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <flux:field>
                <flux:label>From <span class="text-red-500">*</span></flux:label>
                <flux:input 
                    wire:model.blur="document_from" 
                    placeholder="Enter sender..." 
                    type="text" 
                />
            </flux:field>

            <flux:field>
                <flux:label>To <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="document_to_id" placeholder="Choose recipient...">
                    @foreach ($this->offices as $office)
                        <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>

        <div class="mb-6">
            <flux:field>
                <flux:label>Subject <span class="text-red-500">*</span></flux:label>
                <flux:input 
                    wire:model.blur="subject" 
                    type="text" 
                    required 
                    autocomplete="subject" 
                />
            </flux:field>
        </div>

        <div class="mb-8">
            <flux:field>
                <flux:label>
                    Attachment <span class="text-red-500">*</span> 
                    <span class="text-gray-400 font-normal text-xs ml-1">(Max 100MB)</span>
                </flux:label>

                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200 mb-2">
                    <div class="flex flex-col items-center justify-center p-4 text-center">
                        <flux:icon icon="cloud-arrow-up" class="size-6 text-gray-500 mb-2" />
                        <p class="text-xs text-gray-700 mb-1">Click to upload or drag and drop</p>
                        <p class="text-xs text-gray-500">
                            PDF, IMAGES (MAX. 100MB)
                        </p>
                    </div>
                    <input 
                        id="dropzone-file" 
                        type="file" 
                        wire:model="attachment" 
                        accept=".pdf,image/*,.jpg,.jpeg,.png"
                        class="hidden"
                    />
                </label>

                <flux:error name="attachment" />

                <div id="file-list" class="space-y-2">
                    @if ($attachment)
                        <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md border border-gray-200">
                            <div class="flex items-center min-w-0">
                                @php
                                    $mime = $attachment->getClientMimeType();
                                    $icon = str_contains($mime, 'pdf') ? 'document-text' : 
                                           (str_contains($mime, 'image') ? 'photo' : 'document');
                                @endphp
                                <div class="mr-2 flex-shrink-0 text-gray-400">
                                    <flux:icon :icon="$icon" class="size-4" />
                                </div>
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
                                class="text-red-500 hover:text-red-700 p-1"
                            >
                                <flux:icon icon="x-mark" class="size-4" />
                            </button>
                        </div>
                    @endif
                </div>
            </flux:field>
        </div>

        <div class="flex justify-end items-center gap-3 pt-4 border-t border-gray-200">
            <flux:button wire:click="cancel" variant="subtle">Cancel</flux:button>
            <flux:button 
                wire:click.prevent="submitDocument" 
                variant="primary"
                icon="paper-airplane" 
                class="bg-indigo-700 hover:bg-indigo-800 text-white border-transparent w-full sm:w-auto"
            >
                Send
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
                
                // Clear existing list for single file upload if needed, 
                // but since logic handles single 'attachment' wire model, 
                // we just process the first one for visual simplicity if preferred,
                // or loop if you decide to allow multiples later.
                // Here we process the loop as per original code:
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
</div>