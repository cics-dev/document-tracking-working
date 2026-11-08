@props(['column', 'sortBy', 'sortDirection'])

@php
    $active = $sortBy === $column;
    $ascending = $sortDirection === 'asc';
@endphp

<th scope="col" @if ($active) aria-sort="{{ $ascending ? 'ascending' : 'descending' }}" @endif {{ $attributes }}>
    <button type="button" wire:click="sort('{{ $column }}')" class="inline-flex items-center gap-1">
        {{ $slot }}
        <span class="inline-flex">
            <flux:icon icon="arrow-down" variant="micro" class="size-3 {{ $active && ! $ascending ? '' : 'opacity-50' }}" />
            <flux:icon icon="arrow-up" variant="micro" class="size-3 {{ $active && $ascending ? '' : 'opacity-50' }}" />
        </span>
    </button>
</th>
