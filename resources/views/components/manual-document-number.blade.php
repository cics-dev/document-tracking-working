@props(['parts'])

<div class="flex min-h-10 flex-wrap items-center overflow-hidden rounded-lg border border-gray-300 bg-gray-100">
    @foreach($parts as $index => $part)
        @if($part['type'] === 'literal')
            <span class="px-2 py-2 font-medium text-gray-600" wire:key="number-literal-{{ $index }}">{{ $part['value'] }}</span>
        @elseif($part['type'] === 'number')
            <input wire:model.blur="manual_document_sequence" type="number" min="1" required placeholder="No." aria-label="Manual sequence number" class="w-20 self-stretch border-x border-gray-300 bg-white px-2 text-center outline-none focus:ring-2 focus:ring-blue-500" wire:key="number-sequence-{{ $index }}">
        @else
            <input wire:model.blur="manual_document_year" type="number" min="1900" max="9999" required placeholder="Year" aria-label="Manual year" class="w-24 self-stretch border-x border-gray-300 bg-white px-2 text-center outline-none focus:ring-2 focus:ring-blue-500" wire:key="number-year-{{ $index }}">
        @endif
    @endforeach
</div>
<flux:error name="manual_document_sequence" />
<flux:error name="manual_document_year" />
<flux:error name="manual_document_number" />
