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
            <flux:input wire:model="name" label="Document Type Name" placeholder="e.g. Office Memorandum" />
            <flux:input wire:model="abbreviation" label="Abbreviation" placeholder="e.g. IOM" />
            <flux:select wire:model="recipient_mode" label="Recipient Input"><flux:select.option value="office">Office selector</flux:select.option><flux:select.option value="text">Free text</flux:select.option><flux:select.option value="none">None</flux:select.option></flux:select>
            <flux:input wire:model="recipient_label" label="Recipient Label" placeholder="To or For" />
            <flux:input wire:model="recipient_office_key" label="Locked Recipient Workflow Key" placeholder="Leave blank to allow selection" />
            <flux:input wire:model="document_level" label="Document Level" placeholder="Inter, Intra, External" />
            <flux:input wire:model="number_prefix" label="Number Prefix Template" placeholder="{office_with_type}-{type}" />
            <flux:textarea wire:model="content_template" label="Initial Content Template" placeholder="Use {TO} and {SUBJECT}" />
            <div class="space-y-2"><flux:checkbox wire:model="show_thru" label="Show Thru field" /><flux:checkbox wire:model="show_carbon_copy" label="Show carbon-copy offices" /><flux:checkbox wire:model="requires_signatories" label="Require signatories" /><flux:checkbox wire:model="is_publicly_creatable" label="Allow all users to create" /></div>
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
