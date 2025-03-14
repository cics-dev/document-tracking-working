<form wire:submit.prevent="submitDocument" class="mt-6 space-y-6">
    <div class="flex gap-4">
        <div class="flex-1 space-y-2">
            <label for="type-of-document" class="block text-sm font-medium text-gray-700">
                {{ __('Type of Document') }}
            </label>

            <flux:select wire:model="document_type" placeholder="Choose document type...">
                @foreach ($types as $type)
                    <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex-1 space-y-2">
            <label for="To" class="block text-sm font-medium text-gray-700">
                {{ __('To') }}
            </label>

            <flux:select wire:model="document_to" placeholder="Choose document type...">
                @foreach ($users as $user)
                    <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>


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

                <select wire:model="signatories.{{ $index }}.user_id" class="flex-1 border-gray-300 rounded">
                    <option value="">Select Signatory</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>

                <button type="button" wire:click="removeSignatory({{ $index }})" class="text-red-500 hover:underline">
                    Remove
                </button>
            </div>
        @endforeach

        <button type="button" wire:click="addSignatory" class="text-blue-500 hover:underline">
            + Add Signatory
        </button>
    </div>

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
