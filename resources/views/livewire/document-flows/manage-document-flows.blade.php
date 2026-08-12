<div class="w-full space-y-6">
    <flux:heading size="xl">Document Flows</flux:heading>
    <flux:subheading>Configure the ordered routing, signatory, and action stages for each document type.</flux:subheading>
    @if(session('status'))<div class="my-4 rounded bg-green-100 p-3 text-green-800">{{ session('status') }}</div>@endif

    <div class="my-6 max-w-xl">
        <flux:select wire:model.live="documentTypeId" label="Document Type">
            @foreach($types as $type)<flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>@endforeach
        </flux:select>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <form wire:submit="save" class="h-fit space-y-4 rounded-lg border bg-white p-5 shadow-sm">
            <h2 class="font-semibold">{{ $stageId ? 'Edit Stage' : 'Add Stage' }}</h2>
            <div><flux:label>Office</flux:label><x-searchable-filter-select model="officeId" :live="false" :options="$offices->map(fn($o) => ['value'=>(string)$o->id,'label'=>$o->name,'search'=>$o->abbreviation])->all()" placeholder="Choose office..." search-placeholder="Search offices..." /><flux:error name="officeId" /></div>
            <flux:select wire:model.live="stageType" label="Stage Type"><flux:select.option value="routing">Routing</flux:select.option><flux:select.option value="signatory">Signatory</flux:select.option><flux:select.option value="action">Action / Generation</flux:select.option></flux:select>
            @if($stageType === 'signatory')
                <flux:select wire:model="label" label="Stage Label">
                    <flux:select.option value="Recommending Approval">Recommending Approval</flux:select.option>
                    <flux:select.option value="Approved by">Approved by</flux:select.option>
                </flux:select>
                <flux:description>Use Approved by for the President or Recommending Approval for allowed recommending offices.</flux:description>
            @else
                <flux:input wire:model="label" label="Stage Label" placeholder="e.g. VPAF Review" />
            @endif
            <flux:error name="label" />
            <flux:textarea wire:model="description" label="Help Description" placeholder="e.g. For gymnasium or any school facility usage" rows="3" />
            <flux:description>This appears behind a question-mark in Create Document.</flux:description>
            <flux:error name="description" />
            <flux:select wire:model="condition" label="Condition"><flux:select.option value="always">Always</flux:select.option><flux:select.option value="with_budget">With budget implications</flux:select.option><flux:select.option value="without_budget">Without budget implications</flux:select.option></flux:select>
            <flux:checkbox wire:model="isSelectable" label="Show as a checkbox when creating the document" />
            <flux:checkbox wire:model="isRequired" label="MUST complete (automatically included)" />
            <flux:description>Selectable + optional stages may be unchecked. Required stages are always included.</flux:description>
            <div class="flex gap-2"><flux:button type="submit" variant="primary">{{ $stageId ? 'Update' : 'Add' }} Stage</flux:button>@if($stageId)<flux:button type="button" wire:click="resetStage">Cancel</flux:button>@endif</div>
        </form>

        <section class="lg:col-span-2 overflow-hidden rounded-lg border bg-white shadow-sm">
            <table class="w-full text-left text-sm"><thead class="bg-gray-100 text-xs uppercase"><tr><th class="p-3">Stage</th><th class="p-3">Office</th><th class="p-3">Rules</th><th class="p-3 text-right">Actions</th></tr></thead>
                <tbody>@forelse($stages as $stage)<tr class="border-t"><td class="p-3"><b>{{ ucfirst($stage->stage_type) }}</b><br>{{ $stage->label }}@if($stage->description)<br><span class="text-xs text-gray-500">{{ $stage->description }}</span>@endif</td><td class="p-3">{{ $stage->office?->name }}</td><td class="p-3">{{ str_replace('_',' ',ucfirst($stage->condition)) }}<br>{{ $stage->is_required ? 'Must complete' : 'Optional' }} · {{ $stage->is_selectable ? 'Shown to creator' : 'Automatic' }}</td><td class="p-3"><div class="flex justify-end gap-2"><flux:button size="sm" wire:click="edit({{ $stage->id }})">Edit</flux:button><flux:button size="sm" variant="danger" wire:click="delete({{ $stage->id }})" wire:confirm="Delete this flow stage?">Delete</flux:button></div></td></tr>@empty<tr><td colspan="4" class="p-6 text-center text-gray-500">No configured flow. The legacy behavior will be used until stages are added.</td></tr>@endforelse</tbody>
            </table>
        </section>
    </div>
</div>
