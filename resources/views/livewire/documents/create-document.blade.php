<div class="overflow-x-auto rounded-lg shadow-sm">
        <table class="w-full">
<!---End Here-->
    <tbody>
        <tr>
            <td class="border p-2 font-medium text-sm text-gray-700 text-center align-middle">Type of Document</td>
            <td class="border p-2">
                <flux:select wire:model="document_type_id" placeholder="Choose document type..." wire:change="handleUpdateDocumentType">
                    @foreach ($types as $type)
                        <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </td>

            <td class="border p-2 font-medium text-sm text-gray-700 text-center align-middle">To</td>
            <td class="border p-2">
                <flux:select wire:model="document_to_id" placeholder="Choose office...">
                    @foreach ($offices as $office)
                        <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </td>
        </tr>

        <!-- Row: CF Offices (Admin only) -->
        @if ($office_type == 'ADMIN')
        <tr>
            <td class="border p-2 font-medium text-sm text-gray-700 text-center align-middle">CF to Offices</td>
            <td colspan="3" class="border p-2 ">
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex-1">
                        <flux:select wire:model="selected_cf_office" placeholder="Select office...">
                            @foreach ($offices as $office)
                                <flux:select.option value="{{ $office->id }}">{{ $office->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <button type="button" wire:click="addCfOffice" class="text-green-600 hover:text-green-800 text-xl font-bold px-3 py-1 border border-green-600 rounded-full">+</button>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($cf_offices as $officeId)
                        @php $office = $offices->firstWhere('id', $officeId); @endphp
                        @if ($office)
                            <div class="flex items-center bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs">
                                {{ $office->name }}
                                <button type="button" wire:click="removeCfOffice({{ $officeId }})" class="ml-2 text-red-500 hover:text-red-700 font-bold">&minus;</button>
                            </div>
                        @endif
                    @endforeach
                </div>
            </td>
        </tr>
        @endif

        <!-- Row: Subject -->
        <tr>
            <td class="border p-2 font-medium text-sm text-gray-700 text-center align-middle">Subject</td>

            <td colspan="3" class="border p-2">
                <flux:input wire:model="subject" :label="__('')" type="subject" required autocomplete="subject" />
            </td>
        </tr>

        <!-- Row: Content -->
        <tr>
            <td class="border p-2 font-medium text-sm text-gray-700 text-center align-middle">Content</td>
            <td colspan="3" class="border p-2">
                <div wire:ignore>
                    <div id="quill-editor" style="height: 200px;"></div>
                    <input type="hidden" wire:model="content" id="quill-content" />
                </div>
            </td>
        </tr>

        <!-- Row: Signatories -->
        @if ($document_type != 'IOM')
        <tr>
            <td class="border p-2 font-medium text-sm text-gray-700 text-center align-middle">Signatories</td>
            <td colspan="3" class="border p-2 space-y-2">
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

                        <button type="button" wire:click="removeSignatory({{ $index }})" class="text-red-500 hover:underline">Remove</button>
                    </div>
                @endforeach

                <button type="button" wire:click="addSignatory" class="text-blue-500 text-sm hover:underline">
                    + Add Signatory
                </button>
            </td>
        </tr>
        @endif

        <!-- Row: Action Buttons -->
        <tr>
            <td colspan="4" class="border p-2">
                <div class="flex justify-between">
                    <button type="button" wire:click.prevent="submitDocument('draft')" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-orange-700">
                        Save as Draft
                    </button>

                    <div class="flex gap-4 ml-auto">
                        <button type="button" wire:click.prevent="submitDocument('preview')" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Preview
                        </button>
                        <button type="button" wire:click.prevent="submitDocument('send')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Send
                        </button>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
