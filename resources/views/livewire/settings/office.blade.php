<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Office')" :subheading="__('Update your office details and Officer-in-Charge')">
        @if(session('status')) <div class="my-4 rounded bg-green-100 p-3 text-green-800">{{ session('status') }}</div> @endif

        <form wire:submit="save" class="my-6 space-y-8">
            <div>
                <flux:heading size="lg" class="mb-4">Office Information</flux:heading>
                <flux:separator variant="subtle" class="mb-6" />
                <div class="grid gap-4 md:grid-cols-12">
                    <div class="md:col-span-8"><flux:input wire:model="name" label="Office Name" required /><flux:error name="name" /></div>
                    <div class="md:col-span-4"><flux:input wire:model="abbreviation" label="Abbreviation" required /><flux:error name="abbreviation" /></div>
                    <div class="md:col-span-6">
                        <flux:select wire:model="office_type" label="Office Type">
                            <flux:select.option value="ACAD">Academic</flux:select.option>
                            <flux:select.option value="ADMIN">Administration</flux:select.option>
                        </flux:select>
                        <flux:error name="office_type" />
                    </div>
                </div>
            </div>

            <div>
                <flux:heading size="lg" class="mb-4">Officer-in-Charge</flux:heading>
                <flux:separator variant="subtle" class="mb-6" />
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <flux:label>Officer-in-Charge (optional)</flux:label>
                        <x-searchable-filter-select model="acting_head" :live="false"
                            :options="$users->map(fn($user) => ['value' => (string) $user->id, 'label' => $user->name])->values()->all()"
                            placeholder="Use the designated head when blank..." search-placeholder="Search users..." />
                        <flux:error name="acting_head" />
                    </div>
                    @if($acting_head)<flux:button type="button" wire:click="removeActingHead" variant="danger" icon="x-mark" />@endif
                </div>
                <flux:description class="mt-2">Selecting or clearing an OIC takes effect only after you click Update Office.</flux:description>
            </div>

            <div>
                <flux:heading size="lg" class="mb-4">Office Branding</flux:heading>
                <flux:separator variant="subtle" class="mb-6" />
                @if($office_logo)
                    <img src="{{ $office_logo->temporaryUrl() }}" class="mb-3 h-24 w-24 rounded-full border object-cover">
                @elseif($current_logo)
                    <img src="{{ asset('storage/'.$current_logo) }}" class="mb-3 h-24 w-24 rounded-full border object-cover">
                @endif
                <flux:input type="file" wire:model="office_logo" label="Upload Logo" description="PNG or JPG, maximum 2 MB" accept="image/png,image/jpeg" />
                <flux:error name="office_logo" />
            </div>

            <flux:button type="submit" variant="primary">Update Office</flux:button>
        </form>
    </x-settings.layout>
</section>
