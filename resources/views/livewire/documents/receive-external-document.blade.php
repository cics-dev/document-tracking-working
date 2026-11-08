<div class="max-w-7xl mx-auto bg-white rounded-xl shadow-[0_0_10px_0_rgba(0,0,0,0.5)] overflow-hidden p-6 relative">
    
    <div class="mb-8">
        <flux:heading size="xl" class="mb-4 border-b border-gray-200 pb-2">Document Details</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <flux:field>
                <flux:label>From <span class="text-red-500">*</span></flux:label>
                <flux:input 
                    wire:model.blur="document_from" 
                    placeholder="Enter sender..." 
                    type="text" 
                    required
                />
            </flux:field>

            <flux:field>
                <flux:label>To <span class="text-red-500">*</span></flux:label>
                <x-searchable-filter-select model="document_to_id" :live="false"
                    :options="collect($this->offices)->map(fn($office) => ['value' => (string) $office->id, 'label' => $office->name, 'search' => $office->abbreviation])->values()->all()"
                    placeholder="Choose recipient..." search-placeholder="Search offices..." />
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

                <x-document-upload model="attachment" required />

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
                data-form-action
                wire:loading.attr="disabled"
                wire:target="submitDocument"
                wire:click.prevent="submitDocument" 
                variant="primary"
                icon="paper-airplane" 
                class="bg-indigo-700 hover:bg-indigo-800 text-white border-transparent w-full sm:w-auto"
            >
                Send
            </flux:button>
        </div>
    </div>
</div>
