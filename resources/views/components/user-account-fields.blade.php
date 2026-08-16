@props(['offices', 'roles', 'modelPrefix' => '', 'showOffice' => true, 'showIsHead' => true, 'existingSignature' => null])
@php($field = fn (string $name) => $modelPrefix.$name)

<div class="mb-8">
    <flux:heading size="lg" class="mb-4">Personal Information</flux:heading>
    <flux:separator variant="subtle" class="mb-6" />
    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="md:col-span-3"><flux:field><flux:label>Family Name <span class="text-red-500">*</span></flux:label><flux:input wire:model="{{ $field('family_name') }}" required /><flux:error name="{{ $field('family_name') }}" /></flux:field></div>
        <div class="md:col-span-3"><flux:field><flux:label>Given Name <span class="text-red-500">*</span></flux:label><flux:input wire:model="{{ $field('given_name') }}" required /><flux:error name="{{ $field('given_name') }}" /></flux:field></div>
        <div class="md:col-span-3"><flux:input wire:model="{{ $field('middle_name') }}" label="Middle Name" /></div>
        <div class="md:col-span-1"><flux:input wire:model="{{ $field('middle_initial') }}" label="MI" maxlength="1" /><flux:error name="{{ $field('middle_initial') }}" /></div>
        <div class="md:col-span-2"><flux:input wire:model="{{ $field('suffix') }}" label="Suffix" /></div>
        <div class="md:col-span-2"><flux:input wire:model="{{ $field('honorifics') }}" label="Honorifics" placeholder="Mr./Ms." /></div>
        <div class="md:col-span-2"><flux:input wire:model="{{ $field('titles') }}" label="Title" placeholder="PhD, etc." /></div>
        <div class="md:col-span-2"><flux:field><flux:label>Gender <span class="text-red-500">*</span></flux:label><flux:select wire:model="{{ $field('gender') }}" required placeholder="Select..."><flux:select.option value="male">Male</flux:select.option><flux:select.option value="female">Female</flux:select.option><flux:select.option value="other">Other</flux:select.option></flux:select><flux:error name="{{ $field('gender') }}" /></flux:field></div>
        <div class="md:col-span-6"><flux:field><flux:label>Email <span class="text-red-500">*</span></flux:label><flux:input wire:model="{{ $field('email') }}" required type="email" /><flux:error name="{{ $field('email') }}" /></flux:field></div>
    </div>
</div>
<div class="mb-8">
    <flux:heading size="lg" class="mb-4">Professional Information</flux:heading>
    <flux:separator variant="subtle" class="mb-6" />
    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        @if($showOffice)<div class="md:col-span-4"><flux:label>Office <span class="text-red-500">*</span></flux:label><x-searchable-filter-select :model="$field('office_id')" :live="false" :options="$offices->map(fn($office) => ['value' => (string) $office->id, 'label' => $office->name, 'search' => $office->abbreviation])->values()->all()" placeholder="Choose office..." search-placeholder="Search offices..." /><flux:error name="{{ $field('office_id') }}" /></div>@endif
        <div class="md:col-span-4"><flux:field><flux:label>System Role <span class="text-red-500">*</span></flux:label><flux:select wire:model="{{ $field('role_id') }}" required placeholder="Choose role...">@foreach($roles as $role)<flux:select.option value="{{ $role->id }}">{{ $role->role }}</flux:select.option>@endforeach</flux:select><flux:error name="{{ $field('role_id') }}" /></flux:field></div>
        <div class="md:col-span-4"><flux:field><flux:label>Position <span class="text-red-500">*</span></flux:label><flux:input wire:model="{{ $field('position') }}" required /><flux:error name="{{ $field('position') }}" /></flux:field></div>
        @if($showIsHead)<div class="md:col-span-4 flex items-end pb-2"><flux:checkbox wire:model="{{ $field('is_head') }}" label="Is head of office" /></div>@endif
    </div>
</div>
<div class="mb-8">
    <flux:heading size="lg" class="mb-4">Profile Signature</flux:heading>
    <flux:separator variant="subtle" class="mb-6" />
    @if(data_get($this, $field('signature')))<img src="{{ data_get($this, $field('signature'))->temporaryUrl() }}" class="mb-3 h-20 w-40 rounded border object-contain">@elseif($existingSignature)<img src="{{ asset('storage/'.$existingSignature) }}" class="mb-3 h-20 w-40 rounded border object-contain">@endif
    <flux:input type="file" wire:model="{{ $field('signature') }}" label="Upload Signature" accept="image/png, image/jpeg" /><flux:error name="{{ $field('signature') }}" />
</div>
