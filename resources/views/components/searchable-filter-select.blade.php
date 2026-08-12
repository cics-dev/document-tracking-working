@props([
    'model',
    'options' => [],
    'placeholder' => 'All options',
    'searchPlaceholder' => 'Search options...',
    'emptyMessage' => 'No matching options.',
    'disabled' => false,
    'live' => true,
])

<div
    x-data="{
        open: false,
        search: '',
        value: $wire.entangle({{ \Illuminate\Support\Js::from($model) }}){{ $live ? '.live' : '' }},
        options: {{ \Illuminate\Support\Js::from($options) }},
        selectedLabel() {
            return this.options.find((option) => String(option.value) === String(this.value))?.label ?? {{ \Illuminate\Support\Js::from($placeholder) }}
        },
        filteredOptions() {
            const query = this.search.trim().toLowerCase()

            return query
                ? this.options.filter((option) => `${option.label} ${option.search ?? ''}`.toLowerCase().includes(query))
                : this.options
        },
        select(option) {
            this.value = option.value
            this.search = ''
            this.open = false
        },
    }"
    class="relative"
    @click.outside="open = false; search = ''"
    @keydown.escape.stop="open = false; search = ''"
>
    <button
        type="button"
        class="flex h-10 w-full items-center justify-between rounded-lg border border-zinc-200 border-b-zinc-300/80 bg-white px-3 text-left text-sm text-zinc-700 shadow-xs transition hover:border-zinc-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-white/10 dark:bg-white/10 dark:text-zinc-300"
        @click="open = !open; if (open) { $nextTick(() => $refs.search.focus()) }"
        :aria-expanded="open"
        aria-haspopup="listbox"
        @disabled($disabled)
        @class(['opacity-60 cursor-not-allowed' => $disabled])
    >
        <span class="truncate" :class="value ? '' : 'text-zinc-400'" x-text="selectedLabel()"></span>
        <flux:icon icon="chevron-up-down" class="size-4 shrink-0 text-zinc-400" />
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top
        class="absolute z-30 mt-1 w-full min-w-64 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-white/10 dark:bg-zinc-800"
        role="listbox"
    >
        <div class="border-b border-zinc-200 p-2 dark:border-white/10">
            <flux:input x-ref="search" x-model="search" type="search" x-bind:placeholder="{{ \Illuminate\Support\Js::from($searchPlaceholder) }}" aria-label="{{ $searchPlaceholder }}" />
        </div>

        <div class="max-h-64 overflow-y-auto p-1">
            <button
                type="button"
                class="flex w-full items-center rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-white/10"
                :class="!value ? 'bg-zinc-100 dark:bg-white/10' : ''"
                @click="select({ value: '', label: {{ \Illuminate\Support\Js::from($placeholder) }} })"
                role="option"
                :aria-selected="!value"
            >
                {{ $placeholder }}
            </button>

            <template x-for="option in filteredOptions()" :key="option.value">
                <button
                    type="button"
                    class="flex w-full items-center rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-white/10"
                    :class="String(value) === String(option.value) ? 'bg-zinc-100 dark:bg-white/10' : ''"
                    @click="select(option)"
                    role="option"
                    :aria-selected="String(value) === String(option.value)"
                    x-text="option.label"
                ></button>
            </template>

            <p x-show="filteredOptions().length === 0" class="px-3 py-2 text-sm text-zinc-500">
                {{ $emptyMessage }}
            </p>
        </div>
    </div>
</div>
