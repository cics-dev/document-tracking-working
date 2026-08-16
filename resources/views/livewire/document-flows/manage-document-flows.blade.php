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
            <div><flux:label>Office <span class="text-red-500">*</span></flux:label><x-searchable-filter-select model="officeId" :live="false" :options="$offices->map(fn($o) => ['value'=>(string)$o->id,'label'=>$o->name,'search'=>$o->abbreviation])->all()" placeholder="Choose office..." search-placeholder="Search offices..." /><flux:error name="officeId" /></div>
            <flux:select wire:model.live="stageType" label="Stage Type"><flux:select.option value="routing">Routing</flux:select.option><flux:select.option value="signatory">Signatory</flux:select.option><flux:select.option value="action">Action / Generation</flux:select.option></flux:select>
            @if($stageType === 'signatory')
                <flux:select wire:model="label" label="Stage Label">
                    <flux:select.option value="Recommending Approval">Recommending Approval</flux:select.option>
                    <flux:select.option value="Approved by">Approved by</flux:select.option>
                </flux:select>
                <flux:description>Use Approved by for the President or Recommending Approval for allowed recommending offices.</flux:description>
            @else
                <flux:field><flux:label>Stage Label <span class="text-red-500">*</span></flux:label><flux:input wire:model="label" required placeholder="e.g. VPAF Review" /></flux:field>
            @endif
            <flux:error name="label" />
            <flux:textarea wire:model="description" label="Help Description" placeholder="e.g. For gymnasium or any school facility usage" rows="3" />
            <flux:description>This appears behind a question-mark in Create Document.</flux:description>
            <flux:error name="description" />
            <flux:select wire:model.live="workflowConditionId" label="Condition"><flux:select.option value="">Always</flux:select.option>@foreach($conditions->where('is_active', true) as $item)<flux:select.option value="{{ $item->id }}">{{ $item->label }}</flux:select.option>@endforeach</flux:select>
            @if($workflowConditionId)
                <flux:select wire:model="conditionOperator" label="Operator"><flux:select.option value="equals">Equals</flux:select.option><flux:select.option value="not_equals">Does not equal</flux:select.option><flux:select.option value="greater_than">Greater than</flux:select.option><flux:select.option value="less_than">Less than</flux:select.option><flux:select.option value="contains">Contains</flux:select.option></flux:select>
                @if($this->selectedCondition()?->input_type === 'boolean')
                    <flux:select wire:model="conditionValue" label="Expected Value" placeholder="Choose Yes or No"><flux:select.option value="1">Yes</flux:select.option><flux:select.option value="0">No</flux:select.option></flux:select>
                @elseif($this->selectedCondition()?->input_type === 'select')
                    <flux:select wire:model="conditionValue" label="Expected Value">@foreach($this->selectedCondition()?->options ?? [] as $option)<flux:select.option value="{{ $option }}">{{ $option }}</flux:select.option>@endforeach</flux:select>
                @else
                    <flux:input wire:model="conditionValue" :type="$this->selectedCondition()?->input_type === 'number' ? 'number' : 'text'" label="Expected Value" />
                @endif
                <flux:error name="conditionValue" />
            @endif
            <flux:checkbox wire:model="isSelectable" label="Show checkbox" />
            <flux:checkbox wire:model="isRequired" label="Required" />
            <flux:description>Required checkboxes are automatically selected and locked when the condition applies.</flux:description>
            <div class="flex gap-2"><flux:button type="submit" variant="primary">{{ $stageId ? 'Update' : 'Add' }} Stage</flux:button>@if($stageId)<flux:button type="button" wire:click="resetStage">Cancel</flux:button>@endif</div>
        </form>

        <section class="lg:col-span-2 overflow-hidden rounded-lg border bg-white shadow-sm">
            <table class="w-full text-left text-sm"><thead class="bg-gray-100 text-xs uppercase"><tr><th class="p-3">Stage</th><th class="p-3">Office</th><th class="p-3">Rules</th><th class="p-3 text-right">Actions</th></tr></thead>
                <tbody>@forelse($stages as $stage)<tr class="border-t"><td class="p-3"><b>{{ ucfirst($stage->stage_type) }}</b><br>{{ $stage->label }}@if($stage->description)<br><span class="text-xs text-gray-500">{{ $stage->description }}</span>@endif</td><td class="p-3">{{ $stage->office?->name }}</td><td class="p-3">{{ $stage->workflowCondition?->label ?? 'Always' }}@if($stage->workflowCondition) · {{ str_replace('_', ' ', $stage->condition_operator) }} {{ $stage->workflowCondition->input_type === 'boolean' ? ($stage->condition_value ? 'Yes' : 'No') : $stage->condition_value }}@endif<br>{{ $stage->is_required ? 'Must complete' : 'Optional' }} · {{ $stage->is_selectable ? 'Shown to creator' : 'Automatic' }}</td><td class="p-3"><div class="flex justify-end gap-2"><flux:button size="sm" wire:click="edit({{ $stage->id }})">Edit</flux:button><flux:button size="sm" variant="danger" wire:click="delete({{ $stage->id }})" wire:confirm="Delete this flow stage?">Delete</flux:button></div></td></tr>@empty<tr><td colspan="4" class="p-6 text-center text-gray-500">No configured flow.</td></tr>@endforelse</tbody>
            </table>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <form wire:submit="addCondition" class="space-y-4 rounded-lg border bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Add Workflow Condition</h2>
            <flux:field><flux:label>Key <span class="text-red-500">*</span></flux:label><flux:input wire:model="newConditionKey" required placeholder="e.g. uses_external_funding" /><flux:error name="newConditionKey" /></flux:field>
            <flux:field><flux:label>Question / Label <span class="text-red-500">*</span></flux:label><flux:input wire:model="newConditionLabel" required /><flux:error name="newConditionLabel" /></flux:field>
            <flux:select wire:model="newConditionType" label="Input Type"><flux:select.option value="boolean">Yes / No</flux:select.option><flux:select.option value="select">Dropdown</flux:select.option><flux:select.option value="text">Text</flux:select.option><flux:select.option value="number">Number</flux:select.option></flux:select>
            @if($newConditionType === 'select')<flux:input wire:model="newConditionOptions" label="Options (comma separated)" />@endif
            <flux:button type="submit" variant="primary">Add Condition</flux:button>
            @if($conditions->isNotEmpty())<div class="border-t pt-3">@foreach($conditions as $item)<div class="flex items-center justify-between py-1 text-sm"><span>{{ $item->label }} <span class="text-gray-500">({{ $item->input_type }})</span></span><flux:button type="button" size="sm" wire:click="toggleCondition({{ $item->id }})">{{ $item->is_active ? 'Disable' : 'Enable' }}</flux:button></div>@endforeach</div>@endif
        </form>
        <form wire:submit="addGenerationRule" class="space-y-4 rounded-lg border bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Add Document Generation Rule</h2>
            <flux:select wire:model.live="generationContext" label="Source Context"><flux:select.option value="internal">Internal document</flux:select.option><flux:select.option value="external">External document</flux:select.option></flux:select>
            @if($generationContext === 'internal')<flux:field><flux:label>Source Document Type <span class="text-red-500">*</span></flux:label><flux:select wire:model="generationSourceTypeId" required>@foreach($types as $type)<flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>@endforeach</flux:select><flux:error name="generationSourceTypeId" /></flux:field>@endif
            <flux:field><flux:label>Generated Document Type <span class="text-red-500">*</span></flux:label><flux:select wire:model="generationTargetTypeId" required>@foreach($types as $type)<flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>@endforeach</flux:select><flux:error name="generationTargetTypeId" /></flux:field>
            <flux:field><flux:label>Button Label <span class="text-red-500">*</span></flux:label><flux:input wire:model="generationLabel" required placeholder="Generate ECLR" /><flux:error name="generationLabel" /></flux:field>
            <flux:input wire:model="generationStatus" label="Required Source Status" placeholder="Approved (blank for any)" />
            <flux:checkbox wire:model="generationRequiresAssignment" label="Only the assigned action/recipient office can generate" />
            <div><flux:label>Allowed Roles <span class="text-red-500">*</span></flux:label><div class="mt-2 grid gap-2 sm:grid-cols-2">@foreach($roles as $role)<flux:checkbox wire:model="generationRoles" value="{{ $role->id }}" label="{{ $role->description }}" />@endforeach</div><flux:error name="generationRoles" /></div>
            <flux:button type="submit" variant="primary">Add Generation Rule</flux:button>
        </form>
    </div>
    <section class="overflow-hidden rounded-lg border bg-white shadow-sm">
        <h2 class="p-4 font-semibold">Generation Rules</h2>
        <table class="w-full text-left text-sm"><thead class="bg-gray-100"><tr><th class="p-3">Source</th><th class="p-3">Button / Target</th><th class="p-3">Roles</th><th></th></tr></thead><tbody>@foreach($generationRules as $rule)<tr class="border-t {{ $rule->is_active ? '' : 'opacity-50' }}"><td class="p-3">{{ ucfirst($rule->source_context) }}{{ $rule->sourceType ? ': '.$rule->sourceType->name : '' }}</td><td class="p-3">{{ $rule->button_label }} → {{ $rule->targetType?->name }}</td><td class="p-3">{{ $rule->roles->pluck('description')->join(', ') }}</td><td class="p-3 text-right"><div class="flex justify-end gap-2"><flux:button size="sm" wire:click="toggleGenerationRule({{ $rule->id }})">{{ $rule->is_active ? 'Disable' : 'Enable' }}</flux:button><flux:button size="sm" variant="danger" wire:click="deleteGenerationRule({{ $rule->id }})" wire:confirm="Delete this generation rule?">Delete</flux:button></div></td></tr>@endforeach</tbody></table>
    </section>
</div>
