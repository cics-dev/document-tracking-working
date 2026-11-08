<section class="mx-auto w-full max-w-7xl space-y-6">
    <div>
        <flux:heading size="xl">Upload manual document</flux:heading>
        <flux:subheading>Archive a document signed on paper and send informational copies to the people involved.</flux:subheading>
    </div>

    <form wire:submit="archive" class="space-y-5 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center justify-between gap-3 border-b pb-2">
            <h2 class="text-xl font-semibold">Document Details</h2>
            <flux:switch wire:model.live="is_manual_document_number" label="Manual Document No." class="text-sm" />
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <flux:select wire:model.live="documentTypeId" label="Document type" placeholder="Choose a document type" required>
                @foreach ($this->documentTypes as $type)
                    <flux:select.option :value="$type->id">{{ $type->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:field>
                <flux:label>Document Number @if ($is_manual_document_number)<span class="text-red-500">*</span>@endif</flux:label>
                @if ($manualNumberParts !== [])
                    <x-manual-document-number :parts="$manualNumberParts" />
                @elseif ($is_manual_document_number)
                    <flux:input value="Select a document type first" disabled />
                @else
                    <flux:input value="System generated upon archiving" disabled />
                @endif
            </flux:field>

            <flux:input wire:model="subject" label="Subject" maxlength="255" required />
            <flux:input wire:model="signedDate" type="date" label="Date signed on paper (optional)" />
        </div>

        <flux:input wire:model="scan" type="file" label="Signed scan" description="PDF, JPG, or PNG, up to 12 MB." accept=".pdf,.jpg,.jpeg,.png" required />

        <div class="space-y-2">
            <flux:label>CF recipients</flux:label>
            <flux:description>Copies appear in Received Documents for the people you add.</flux:description>
            <div class="flex items-start gap-3">
                <div class="min-w-0 flex-1">
                    <x-searchable-filter-select
                        model="selectedRecipient"
                        :options="$recipientOptions"
                        :live="false"
                        :open-upward="true"
                        placeholder="Choose a recipient"
                        search-placeholder="Search by name or office"
                    />
                </div>
                <flux:button type="button" wire:click="addRecipient" icon="plus">Add CF</flux:button>
            </div>
            <flux:error name="selectedRecipient" />
            @if ($recipients->isNotEmpty())
                <ul class="flex flex-wrap gap-2">
                    @foreach ($recipients as $recipient)
                        <li wire:key="archive-cf-{{ $recipient->id }}">
                            <flux:button type="button" size="sm" icon:trailing="x-mark" wire:click="removeRecipient({{ $recipient->id }})" aria-label="Remove {{ $recipient->name }}">
                                {{ $recipient->name }}
                            </flux:button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex justify-end gap-2">
            <flux:button :href="route('documents.list-documents', ['mode' => 'Sent'])" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="scan, archive">Archive document</flux:button>
        </div>
    </form>
</section>
