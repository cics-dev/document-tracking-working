<section class="w-full min-w-0 space-y-6">
    <div>
        <flux:heading size="xl">Document Types</flux:heading>
        <flux:subheading>Create and maintain the document types available throughout the system.</flux:subheading>
    </div>

    @if (session('status'))
        <div class="rounded-lg bg-green-100 p-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    <flux:error name="delete" />

    <form wire:submit="save" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <flux:field><flux:label>Document Type Name <span class="text-red-500">*</span></flux:label><flux:input wire:model="name" required placeholder="e.g. Office Memorandum" /><flux:error name="name" /></flux:field>
            <flux:field><flux:label>Abbreviation <span class="text-red-500">*</span></flux:label><flux:input wire:model="abbreviation" required placeholder="e.g. IOM" /><flux:error name="abbreviation" /></flux:field>
            <flux:field><flux:label>Recipient Input <span class="text-red-500">*</span></flux:label><flux:select wire:model="recipient_mode" required><flux:select.option value="office">Office selector</flux:select.option><flux:select.option value="text">Free text</flux:select.option><flux:select.option value="none">None</flux:select.option></flux:select><flux:error name="recipient_mode" /></flux:field>
            <flux:field><flux:label>Recipient Label <span class="text-red-500">*</span></flux:label><flux:input wire:model="recipient_label" required placeholder="To or For" /><flux:error name="recipient_label" /></flux:field>
            <flux:field x-data="{ showHelp: false }">
                <div class="flex items-center gap-2">
                    <flux:label>Always Send To Office</flux:label>
                    <div class="relative">
                        <button type="button" @click="showHelp = !showHelp" @click.outside="showHelp = false" class="flex size-5 items-center justify-center rounded-full border border-indigo-300 text-xs font-bold text-indigo-600 hover:bg-indigo-50" aria-label="About the fixed recipient office">?</button>
                        <div x-cloak x-show="showHelp" x-transition class="absolute left-0 z-30 mt-2 w-72 rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700 shadow-lg">
                            <b>Always Send To Office</b><br>Select an office when every document of this type must go there. The office's current Head or OIC will handle its workflow steps. Leave it blank when the sender should choose the recipient.
                        </div>
                    </div>
                </div>
                <x-searchable-filter-select model="recipient_office_id" :live="false"
                    :options="$recipientOffices->map(fn($office) => [
                        'value' => (string) $office->id,
                        'label' => $office->name,
                        'search' => $office->abbreviation,
                    ])->values()->all()"
                    placeholder="Sender chooses the recipient"
                    search-placeholder="Search offices..." />
                <flux:error name="recipient_office_id" />
            </flux:field>
            <flux:field x-data="{ showHelp: false }">
                <div class="flex items-center gap-2">
                    <flux:label>Document Level <span class="text-red-500">*</span></flux:label>
                    <div class="relative">
                        <button type="button" @click="showHelp = !showHelp" @click.outside="showHelp = false" class="flex size-5 items-center justify-center rounded-full border border-indigo-300 text-xs font-bold text-indigo-600 hover:bg-indigo-50" aria-label="About document levels">?</button>
                        <div x-cloak x-show="showHelp" x-transition class="absolute left-0 z-30 mt-2 w-72 rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700 shadow-lg">
                            <b>Document Level</b><br>Inter Office documents will route to other offices. Intra Office documents will remain within your office.
                        </div>
                    </div>
                </div>
                <flux:select wire:model="document_level" required>
                    <flux:select.option value="Inter">Inter Office</flux:select.option>
                    <flux:select.option value="Intra">Intra Office</flux:select.option>
                </flux:select>
                <flux:error name="document_level" />
            </flux:field>
            <flux:input wire:model="number_prefix" label="Number Prefix Template" placeholder="{office_with_type}-{type}" />
            <flux:select wire:model="print_layout" label="Print Layout">
                <flux:select.option value="memorandum">Memorandum</flux:select.option>
                <flux:select.option value="letter">Letter</flux:select.option>
                <flux:select.option value="indorsement">Indorsement</flux:select.option>
                <flux:select.option value="special_order">Special Order</flux:select.option>
            </flux:select>
            <flux:select wire:model="sender_signature_policy" label="Sender Signature">
                <flux:select.option value="approved">Only when approved</flux:select.option>
                <flux:select.option value="always">Always</flux:select.option>
                <flux:select.option value="never">Never</flux:select.option>
            </flux:select>
            <flux:select wire:model="approver_display_mode" label="Approver Display">
                <flux:select.option value="action_box">Approved / Disapproved action box</flux:select.option>
                <flux:select.option value="labeled">Show “Approved by” label</flux:select.option>
                <flux:select.option value="signature_only">Signature and name only</flux:select.option>
                <flux:select.option value="hidden">Hidden on paper (workflow only)</flux:select.option>
            </flux:select>
            <flux:textarea wire:model="content_template" label="Initial Content Template" placeholder="Use {TO} and {SUBJECT}" />
            <div class="space-y-2"><flux:checkbox wire:model="show_thru" label="Show Thru field" /><flux:checkbox wire:model="show_carbon_copy" label="Allow CF" /><flux:checkbox wire:model="allow_attachments" label="Allow uploading attachments" /><flux:checkbox wire:model="requires_signatories" label="Require signatories" /><flux:checkbox wire:model="allow_oic_signature" label="Allow OIC to sign for the office head" /><flux:checkbox wire:model="is_publicly_creatable" label="Allow all users to create" /></div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <flux:button type="submit" variant="primary" icon="check">{{ $editingId ? 'Update Document Type' : 'Add Document Type' }}</flux:button>
            @if ($editingId)
                <flux:button type="button" wire:click="resetForm" variant="subtle">Cancel</flux:button>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-gray-500">
                <tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Abbreviation</th><th class="px-5 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($types as $type)
                    <tr>
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $type->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $type->abbreviation }}</td>
                        <td class="px-5 py-3"><div class="flex justify-end gap-2"><flux:button size="sm" wire:click="edit({{ $type->id }})" icon="pencil-square">Edit</flux:button><flux:button size="sm" wire:click="delete({{ $type->id }})" wire:confirm="Delete this document type?" variant="danger" icon="trash">Delete</flux:button></div></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500">No document types yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
