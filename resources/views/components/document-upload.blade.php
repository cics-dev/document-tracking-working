@props(['model', 'multiple' => false, 'required' => false])

<div
    x-data="{ uploading: false, progress: null, dragging: false, property: @js($model) }"
    x-on:livewire-upload-start="uploading = true; progress = null"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-error="uploading = false"
    x-on:livewire-upload-cancel="uploading = false"
>
    <label
        class="flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 transition-colors hover:bg-gray-100"
        :class="{ 'border-green-500 bg-green-50': dragging }"
        x-on:dragover.prevent="dragging = !uploading"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="
            dragging = false;
            if (!uploading && $event.dataTransfer.files.length) {
                $refs.file.files = $event.dataTransfer.files;
                $refs.file.dispatchEvent(new Event('change', { bubbles: true }));
            }
        "
    >
        <div class="flex flex-col items-center justify-center p-4 text-center">
            <flux:icon icon="cloud-arrow-up" class="mb-2 size-6 text-gray-500" />
            <p class="mb-1 text-xs text-gray-700">Click to upload or drag and drop</p>
            <p class="text-xs text-gray-500">PDF, DOCX, IMAGES</p>
        </div>

        <input
            type="file"
            wire:model="{{ $model }}"
            x-ref="file"
            :disabled="uploading"
            @if ($multiple) multiple @endif
            @if ($required) required @endif
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp"
            class="sr-only"
        />
    </label>

    <div x-cloak x-show="uploading" class="mt-2 space-y-2 rounded-md border border-gray-200 bg-white p-3">
        <div class="flex items-center justify-between gap-2">
            <p role="status" class="text-sm text-gray-700" x-text="progress === null ? 'Preparing upload…' : (progress === 100 ? 'Checking upload…' : 'Uploading…')"></p>
            <flux:button type="button" variant="ghost" size="xs" x-bind:disabled="progress === null || progress === 100" x-on:click="$wire.$cancelUpload(property)">Cancel upload</flux:button>
        </div>
        <progress class="block h-2 w-full" max="100" :value="progress" aria-label="Upload progress"></progress>
    </div>
</div>
