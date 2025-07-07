<form wire:submit.prevent="submitDocument" class="mt-6 space-y-6">
    <div class="flex gap-4">
        <div class="flex-1 space-y-2">
            <label for="type-of-document" class="block text-sm font-medium text-gray-700">
                {{ __('Type of Document') }}
            </label>

            <flux:select wire:model="document_type_id" placeholder="Choose document type..." wire:change="handleUpdateDocumentType">
                @foreach ($types as $type)
                    <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex-1 space-y-2">
            <label for="To" class="block text-sm font-medium text-gray-700">
                {{ __('To') }}
            </label>

            <flux:select wire:model="document_to_id" placeholder="Choose recipient...">
                @foreach ($offices as $office)
                    <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    @if ($office_type == 'ADMIN')
        <div class="space-y-4">
            <label class="block text-sm font-medium text-gray-700">
                {{ __('CF to Offices') }}
            </label>

            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <flux:select wire:model="selected_cf_office" placeholder="Select office...">
                        @foreach ($offices as $office)
                            <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <button type="button" wire:click="addCfOffice" class="text-green-600 hover:text-green-800 text-lg font-bold">
                    +
                </button>
            </div>

            {{-- Display CF Office Chips --}}
            <div class="flex flex-wrap gap-2">
                @foreach ($cf_offices as $officeId)
                    @php
                        $office = $offices->firstWhere('id', $officeId);
                    @endphp
                    @if ($office)
                        <div class="flex items-center bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs">
                            {{ $office->name }}
                            <button type="button" wire:click="removeCfOffice({{ $officeId }})" class="ml-2 text-red-500 hover:text-red-700 font-bold">
                                &minus;
                            </button>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif



    <flux:input
        wire:model="thru"
        :label="__('Thru')"
        type="thru"
        required
        autocomplete="thru"
    />

    <flux:input
        wire:model="subject"
        :label="__('Subject')"
        type="subject"
        required
        autocomplete="subject"
    />

    <div wire:ignore>
        <div id="quill-editor" style="height: 200px;"></div>

        <input type="hidden" wire:model="content" id="quill-content" />
    </div>

    @if ($document_type != 'IOM')
        <div class="space-y-4">
            <label class="block text-sm font-medium text-gray-700">
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
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="budget_office" class="ml-2 block text-sm text-gray-700">
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
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="motor_pool" class="ml-2 block text-sm text-gray-700">
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
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="legal_review" class="ml-2 block text-sm text-gray-700">
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
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="igp_review" class="ml-2 block text-sm text-gray-700">
                            IGP Review
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="space-y-4">
            <label class="block text-sm font-medium text-gray-700">
                {{ __('Signatories') }}
            </label>

            @foreach ($signatories as $index => $signatory)
                <div class="flex items-center gap-4">
                    <select wire:model="signatories.{{ $index }}.role" class="flex-1 border-gray-300 text-sm font-light text-gray-700">
                        <option value="">Select Role</option>
                        <option value="Recommending Approval">Recommending Approval</option>
                        <option value="Reviewed by">Reviewed by</option>
                        <option value="Noted by">Noted by</option>
                        <option value="Approved by">Approved by</option>
                        <option value="Concurred by">Concurred by</option>
                    </select>

                    <select wire:model="signatories.{{ $index }}.office_id" class="flex-1 border-gray-300 rounded">
                        <option value="">Select Signatory</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>

                    <button type="button" wire:click="removeSignatory({{ $index }})" class="text-red-500 hover:underline">
                        Remove
                    </button>
                </div>
            @endforeach

            <button type="button" wire:click="addSignatory" class="text-blue-500 text-sm hover:underline">
                + Add Signatory
            </button>
        </div>
    @endif

    <div class="mt-4 flex justify-between">
        <button type="button" wire:click.prevent="submitDocument('draft')" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            Save as Draft
        </button>

        <div class="flex gap-4 ml-auto">
            <button type="button" wire:click.prevent="submitDocument('preview')" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Preview
            </button>
            <button type="button" wire:click.prevent="submitDocument('send')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Send
            </button>
        </div>
    </div>
</form>