<form wire:submit.prevent="submitDocument('send')" data-document-editor data-configured-document-type="{{ $document_type_id }}" class="max-w-7xl mx-auto bg-white rounded-xl shadow-[0_0_10px_0_rgba(0,0,0,0.5)] overflow-hidden p-6 relative">
    
    <div class="mb-8">
        <div class="flex items-center justify-between border-b pb-2 mb-4">
            <h2 class="text-xl font-semibold text-gray-900">{{ $revision_document_number ?: 'Document Details' }}</h2>
            
            @if($redirect_mode != 'revision')
                <div class="flex items-center">
                    <flux:switch 
                        wire:model.live="is_manual_document_number" 
                        label="Manual Document No." 
                        class="text-sm"
                    />
                </div>
            @endif
        </div>

        @if($senderOfficeOptions->count() > 1)
            <div class="mb-4">
                <flux:select wire:model.live="document_from_id" label="From office" placeholder="Choose issuing office..." required>
                    @foreach($senderOfficeOptions as $senderOffice)
                        <flux:select.option value="{{ $senderOffice->id }}">
                            {{ $senderOffice->name }} — {{ $senderOffice->acting_head_id === auth()->id() ? 'OIC' : ($senderOffice->head_id === auth()->id() ? 'Head' : 'Member') }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="document_from_id" />
            </div>
        @endif

        @if($redirect_mode != 'revision')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:field>
                        <flux:label>Document Number @if($is_manual_document_number)<span class="text-red-500">*</span>@endif</flux:label>
                        
                        @if($is_manual_document_number)
                            <x-manual-document-number :parts="$this->manualDocumentNumberParts()" />
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
        @endif
    </div>

    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <flux:field>
                    <flux:label>Type of Document <span class="text-red-500">*</span></flux:label>
                    <flux:select 
                        wire:model.live="document_type_id"
                        data-document-type
                        placeholder="Choose document type..." 
                        :disabled="$original_document_number ? true : false"
                    >
                        @foreach ($types as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            @if ($selectedDocumentType && $selectedDocumentType->recipient_mode !== 'none')
                @if ($selectedDocumentType->recipient_mode === 'office')
                    <div>
                        <flux:field>
                            <flux:label>{{ $selectedDocumentType->recipient_label }} <span class="text-red-500">*</span></flux:label>
                            <x-searchable-filter-select model="document_to_id" :live="false" :disabled="(bool) $selectedDocumentType->recipient_office_id"
                                :options="$offices->map(fn($office) => ['value' => (string) $office->id, 'label' => $office->name, 'search' => $office->abbreviation])->values()->all()"
                                placeholder="Choose recipient..." search-placeholder="Search offices..." />
                        </flux:field>
                    </div>
                @elseif ($selectedDocumentType->recipient_mode === 'text')
                    <div>
                        <flux:field>
                            <flux:label>{{ $selectedDocumentType->recipient_label }} <span class="text-red-500">*</span></flux:label>
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

    @if ($selectedDocumentType?->show_carbon_copy)
    <div class="mb-8 rounded-lg border border-gray-200 bg-gray-50/80 p-5">
        <div class="mb-3">
            <flux:label>CF to Offices</flux:label>
            <flux:description>Send informational copies without adding these offices to the approval route.</flux:description>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex-1">
                <x-searchable-filter-select model="selected_cf_office" :live="false"
                    :options="$this->availableCfOfficeOptions()"
                    placeholder="Choose an office to receive a copy..." search-placeholder="Search office name or abbreviation..." />
            </div>
            <flux:button type="button" wire:click="addCfOffice" wire:loading.attr="disabled" :disabled="blank($selected_cf_office)" icon="plus" variant="primary">Add CF</flux:button>
        </div>

        @if(is_array($cf_offices) && count($cf_offices) > 0)
        <div class="mt-4 flex flex-wrap gap-2" wire:key="cf-office-list">
            @foreach ($cfOfficeRecords as $office)
                    <flux:badge wire:key="cf-office-{{ $office->id }}" size="lg" icon-trailing="x-mark" wire:click="removeCfOffice({{ $office->id }})" class="cursor-pointer bg-indigo-100 text-indigo-900 transition-colors hover:bg-red-100 hover:text-red-700">
                        {{ $office->name }}@if($office->abbreviation) ({{ $office->abbreviation }})@endif
                    </flux:badge>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    <div class="mb-8">
        @if ($selectedDocumentType?->show_thru)
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
            <div wire:ignore class="border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                <div id="quill-editor" style="height: clamp(200px, 40vh, 320px);" class="bg-white text-gray-900"></div>
                <input type="hidden" wire:model="content" id="quill-content" value="{{ $content }}" />
            </div>
            <flux:error name="content" />
        </div>
    </div>

    @if($flowConditions->isNotEmpty())
        <div class="mb-8 rounded-lg border border-indigo-100 bg-indigo-50/40 p-5">
            <flux:label class="mb-3">Document Conditions</flux:label>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($flowConditions as $condition)
                    @if($condition['input_type'] === 'boolean')
                        <flux:checkbox wire:model.live="conditionValues.{{ $condition['id'] }}" :label="$condition['label']" />
                    @elseif($condition['input_type'] === 'select')
                        <flux:select wire:model="conditionValues.{{ $condition['id'] }}" :label="$condition['label']" placeholder="Choose an option...">
                            @foreach($condition['options'] ?? [] as $option)
                                <flux:select.option value="{{ $option }}">{{ $option }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    @else
                        <flux:input wire:model="conditionValues.{{ $condition['id'] }}" :type="$condition['input_type'] === 'number' ? 'number' : 'text'" :label="$condition['label']" />
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if ($selectedDocumentType)
        @if ($this->applicableFlowStages()->where('stage_type', 'routing')->where('is_selectable', true)->isNotEmpty())
        <div class="mb-8 bg-gray-50/80 p-5 rounded-lg border border-gray-100">
            <flux:label class="mb-3">Routing Requirements <span class="text-gray-500 font-normal">- select applicable review offices</span></flux:label>
            <div class="h-4"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($this->applicableFlowStages()->where('stage_type', 'routing')->where('is_selectable', true)->values()->chunk(2) as $routingColumn)
                        <div class="space-y-3">
                            @foreach($routingColumn as $stage)
                                <div class="flex items-center gap-1">
                                    <flux:checkbox wire:model="selectedFlowStages.{{ $stage['id'] }}" :label="$stage['label'].($this->conditionLocksStage($stage) ? ' (Required)' : '')" :disabled="$this->conditionLocksStage($stage) || ($stage['is_required'] && empty($stage['workflow_condition_id']))" />
                                    @if(!empty($stage['description']))
                                        <flux:tooltip :content="$stage['description']" position="top">
                                            <button type="button" class="flex size-5 items-center justify-center rounded-full border border-indigo-300 text-xs font-bold text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/30" aria-label="{{ $stage['label'] }} help">?</button>
                                        </flux:tooltip>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                @endforeach
            </div>
        </div>
        @endif

        @php
            $choiceGroups = $this->flowChoiceGroups();
        @endphp

        @if (! $this->isIntraDocument() && ($selectedDocumentType->requires_signatories || $this->applicableFlowStages()->where('stage_type', 'signatory')->isNotEmpty()))
        <div class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-1">
                    <flux:heading size="lg">Signatories</flux:heading>
                    @if($selectedDocumentType->requires_signatories || $choiceGroups->contains('required', true))<span class="text-red-500">*</span>@endif
                </div>
                <flux:button wire:click="addSignatory" wire:loading.attr="disabled" variant="ghost" size="sm" icon="plus" class="text-indigo-600">Add Signatory</flux:button>
            </div>
            
            <flux:separator variant="subtle" class="mb-4" />

            <flux:error name="signatories" class="mb-2" />

            <div class="space-y-3">
                @foreach ($choiceGroups as $groupKey => $group)
                    @php
                        $selectedStage = $group['stages']->firstWhere('id', (int) ($flowGroupSelections[$groupKey] ?? 0));
                        $groupLabels = $group['stages']->pluck('label')->unique();
                        $approvalLabel = $selectedStage?->label ?? ($groupLabels->count() === 1 ? $groupLabels->first() : $group['label']);
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start" wire:key="flow-choice-{{ $groupKey }}">
                        <div class="md:col-span-4">
                            <flux:input :value="$approvalLabel" disabled aria-label="Signatory role" />
                        </div>
                        <div class="md:col-span-7">
                            <x-searchable-filter-select model="flowGroupSelections.{{ $groupKey }}" :live="true"
                                :options="$group['options']" placeholder="Select signatory office..." search-placeholder="Search offices..." />
                            <flux:error name="flowGroupSelections.{{ $groupKey }}" />
                        </div>
                        <div class="md:col-span-1 flex justify-end">
                            @if($group['required'])
                                <div class="mt-2 text-gray-400" title="Required Signatory"><flux:icon icon="lock-closed" class="size-5" /></div>
                            @endif
                        </div>
                    </div>
                @endforeach
                @foreach ($signatories as $index => $signatory)
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                        <div class="md:col-span-4">
                            @if($signatory['locked'] ?? false)
                                <flux:input wire:model="signatories.{{ $index }}.role" disabled />
                            @else
                                <flux:select wire:model.live="signatories.{{ $index }}.role_type" placeholder="Select role">
                                    <flux:select.option value="Reviewed by">Reviewed by</flux:select.option>
                                    <flux:select.option value="Recommending Approval">Recommending Approval</flux:select.option>
                                    <flux:select.option value="custom">Custom label</flux:select.option>
                                </flux:select>
                                @if(($signatory['role_type'] ?? '') === 'custom')
                                    <flux:input wire:model.live="signatories.{{ $index }}.role" class="mt-2" placeholder="Enter custom label..." />
                                @endif
                            @endif
                            <flux:error name="signatories.{{ $index }}.role" />
                        </div>

                        <div class="md:col-span-7">
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    @if($signatory['locked'] ?? false)
                                        <flux:input :value="collect($allOffices)->firstWhere('id', $signatory['office_id'])?->name ?? 'Office unavailable'" disabled />
                                    @else
                                        <x-searchable-filter-select model="signatories.{{ $index }}.office_id" :live="true"
                                            :options="$this->signatoryOfficeOptions($signatory)"
                                            placeholder="Select signatory office..." search-placeholder="Search offices..." />
                                    @endif
                                </div>
                                @if($this->configuredSignatoryDescription($signatory))
                                    <flux:tooltip :content="$this->configuredSignatoryDescription($signatory)['description']" position="top">
                                        <button type="button" class="flex size-5 items-center justify-center rounded-full border border-indigo-300 text-xs font-bold text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/30" aria-label="{{ $this->configuredSignatoryDescription($signatory)['label'] }} help">?</button>
                                    </flux:tooltip>
                                @endif
                            </div>
                            <flux:error name="signatories.{{ $index }}.office_id" />
                        </div>

                        <div class="md:col-span-1 flex justify-end">
                            @if(!($signatory['locked'] ?? false))
                                <flux:button wire:click="removeSignatory({{ $index }})" wire:loading.attr="disabled" icon="trash" variant="subtle" class="text-red-500 hover:text-red-700 hover:bg-red-50 mt-1" />
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

        @if ($selectedDocumentType->allow_attachments)
        <div class="mb-8">
            <flux:label class="mb-2">Attachments <span class="text-gray-400 text-xs font-normal">(Max 100MB per file)</span></flux:label>

            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-1/3">
                    <x-document-upload model="attachments" multiple />
                </div>

                <div class="w-full md:w-2/3">
                    @error('attachments.*') 
                        <flux:error name="attachments" />
                    @enderror

                    <div id="file-list" class="space-y-2">
                        @if (!empty($attachments) || !empty($existingAttachments))
                            @foreach ($existingAttachments as $file)
                                <div wire:key="attachment-{{ $file['id'] }}" class="flex items-center justify-between bg-blue-50 px-3 py-2 rounded-md border border-blue-200">
                                    <div class="flex items-center min-w-0">
                                        <div class="mr-2 flex-shrink-0 text-blue-500">
                                            <flux:icon icon="document" class="size-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-gray-900 truncate">
                                                {{ $file['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                EXISTING FILE • {{ strtoupper($file['file_type']) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <button type="button"
                                            wire:click="viewAttachment({{ $file['id'] }}, '{{ $file['type'] }}')" wire:loading.attr="disabled">
                                            <flux:icon.eye class="w-5 h-5" />
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click="removeExistingAttachment({{ $file['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="text-red-500 hover:text-red-700 p-1"
                                        >
                                            <flux:icon icon="x-mark" class="size-4" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
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
                                        wire:loading.attr="disabled"
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
    @endif

    <p wire:loading.delay wire:target="document_type_id,conditionValues,document_from_id" role="status" class="mb-3 text-sm text-gray-600">
        Loading document settings…
    </p>

    <fieldset wire:loading.attr="disabled" wire:target="document_type_id,conditionValues,document_from_id,previewDocument,submitDocument" class="flex min-w-0 flex-col-reverse items-center justify-between gap-4 border-t border-gray-200 pt-6 disabled:opacity-60 sm:flex-row">
        <flux:button 
            data-form-action
            wire:click.prevent="submitDocument('Draft')" 
            wire:loading.attr="disabled"
            wire:target="submitDocument"
            variant="primary"
            icon="document-text" 
            class="bg-gray-700 hover:bg-gray-800 text-white border-transparent w-full sm:w-auto"
        >
            <span wire:loading.remove wire:target="submitDocument('Draft')">Save as Draft</span>
            <span wire:loading wire:target="submitDocument('Draft')">Saving...</span>
        </flux:button>

        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <flux:button 
                data-form-action
                wire:click.prevent="previewDocument()" 
                wire:loading.attr="disabled"
                wire:target="previewDocument"
                variant="primary"
                icon="eye" 
                class="bg-gray-300 hover:bg-gray-400 text-gray-900 border-transparent w-full sm:w-auto"
            >
                <span wire:loading.remove wire:target="previewDocument">Preview</span>
                <span wire:loading wire:target="previewDocument">Loading...</span>
            </flux:button>
            
            @if($headIsTemporarilyRelieved)
                <div class="max-w-md text-sm text-amber-700 dark:text-amber-300">
                    This office currently has an OIC. You may save and preview this draft; the OIC must send it.
                </div>
            @else
            <flux:button 
                type="submit" 
                data-form-action
                wire:click.prevent="submitDocument('send')" 
                wire:loading.attr="disabled"
                wire:target="submitDocument"
                variant="primary"
                icon="paper-airplane" 
                class="bg-indigo-700 hover:bg-indigo-800 text-white border-transparent w-full sm:w-auto"
            >
                <span wire:loading.remove wire:target="submitDocument('send')">@if (($selectedDocumentType?->document_level ?? 'Inter') !== 'Intra') Send @else Save @endif</span>
                <span wire:loading wire:target="submitDocument('send')">Processing...</span>
            </flux:button>
            @endif
        </div>
    </fieldset>

    <flux:modal name="view-attachment-modal" class="max-w-5xl w-full">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Attachment Preview</flux:heading>
            </div>
            <p wire:loading wire:target="viewAttachment" class="p-4 text-gray-500" role="status">Loading attachment...</p>
            @if ($selectedAttachment)
                <div class="border rounded-lg overflow-hidden">
                    @if ($selectedAttachment->file_type == 'pdf')
                        @if ($attachmentPreviewUrl)
                            <x-attachment-preview-media :url="$attachmentPreviewUrl" type="pdf" />
                        @else
                            <p>Loading preview...</p>
                        @endif
                    @else
                        <x-attachment-preview-media :url="$attachmentPreviewUrl" type="image" />
                    @endif
                </div>
            @endif
        </div>
    </flux:modal>
</form>
