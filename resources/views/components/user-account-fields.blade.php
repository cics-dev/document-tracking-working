@props(['offices', 'roles', 'modelPrefix' => '', 'showOffice' => true, 'showIsHead' => true, 'existingSignature' => null])
@php($field = fn (string $name) => $modelPrefix.$name)

<div class="mb-8">
    <flux:heading size="lg" class="mb-4">Personal Information</flux:heading>
    <flux:separator variant="subtle" class="mb-6" />
    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="md:col-span-3"><flux:input wire:model="{{ $field('family_name') }}" label="Family Name *" /><flux:error name="{{ $field('family_name') }}" /></div>
        <div class="md:col-span-3"><flux:input wire:model="{{ $field('given_name') }}" label="Given Name *" /><flux:error name="{{ $field('given_name') }}" /></div>
        <div class="md:col-span-3"><flux:input wire:model="{{ $field('middle_name') }}" label="Middle Name" /></div>
        <div class="md:col-span-1"><flux:input wire:model="{{ $field('middle_initial') }}" label="MI" maxlength="1" /><flux:error name="{{ $field('middle_initial') }}" /></div>
        <div class="md:col-span-2"><flux:input wire:model="{{ $field('suffix') }}" label="Suffix" /></div>
        <div class="md:col-span-2"><flux:input wire:model="{{ $field('honorifics') }}" label="Honorifics" placeholder="Mr./Ms." /></div>
        <div class="md:col-span-2"><flux:input wire:model="{{ $field('titles') }}" label="Title" placeholder="PhD, etc." /></div>
        <div class="md:col-span-2"><flux:select wire:model="{{ $field('gender') }}" label="Gender" placeholder="Select..."><flux:select.option value="male">Male</flux:select.option><flux:select.option value="female">Female</flux:select.option><flux:select.option value="other">Other</flux:select.option></flux:select><flux:error name="{{ $field('gender') }}" /></div>
        <div class="md:col-span-6"><flux:input wire:model="{{ $field('email') }}" label="Email *" type="email" /><flux:error name="{{ $field('email') }}" /></div>
    </div>
</div>
<div class="mb-8">
    <flux:heading size="lg" class="mb-4">Professional Information</flux:heading>
    <flux:separator variant="subtle" class="mb-6" />
    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        @if($showOffice)<div class="md:col-span-4"><flux:label>Office *</flux:label><x-searchable-filter-select :model="$field('office_id')" :live="false" :options="$offices->map(fn($office) => ['value' => (string) $office->id, 'label' => $office->name, 'search' => $office->abbreviation])->values()->all()" placeholder="Choose office..." search-placeholder="Search offices..." /><flux:error name="{{ $field('office_id') }}" /></div>@endif
        <div class="md:col-span-4"><flux:select wire:model="{{ $field('role_id') }}" label="System Role *" placeholder="Choose role...">@foreach($roles as $role)<flux:select.option value="{{ $role->id }}">{{ $role->role }}</flux:select.option>@endforeach</flux:select><flux:error name="{{ $field('role_id') }}" /></div>
        <div class="md:col-span-4"><flux:input wire:model="{{ $field('position') }}" label="Position *" /><flux:error name="{{ $field('position') }}" /></div>
        @if($showIsHead)<div class="md:col-span-4 flex items-end pb-2"><flux:checkbox wire:model="{{ $field('is_head') }}" label="Is head of office" /></div>@endif
    </div>
</div>
<div class="mb-8">
    <flux:heading size="lg" class="mb-4">Profile Signature</flux:heading>
    <flux:separator variant="subtle" class="mb-6" />
    @if(data_get($this, $field('signature')))<img src="{{ data_get($this, $field('signature'))->temporaryUrl() }}" class="mb-3 h-20 w-40 rounded border object-contain">@elseif($existingSignature)<img src="{{ asset('storage/'.$existingSignature) }}" class="mb-3 h-20 w-40 rounded border object-contain">@endif
    <flux:input type="file" wire:model="{{ $field('signature') }}" label="Upload Signature" accept="image/png, image/jpeg" /><flux:error name="{{ $field('signature') }}" />
</div>
