@props(['url', 'type'])

<div wire:key="attachment-media-{{ md5($type.'|'.$url) }}"
    wire:loading.remove wire:target="viewAttachment"
    x-data="{ ready: false }">
    <p x-show="!ready" class="p-4 text-gray-500" role="status">Loading attachment...</p>
    @if($type === 'pdf')
        <iframe src="{{ $url }}" x-cloak x-show="ready" style="display: none" @load="ready = true"
            class="w-full h-[600px] border rounded" frameborder="0" title="Attachment preview"></iframe>
    @else
        <img src="{{ $url }}" x-cloak x-show="ready" style="display: none" @load="ready = true"
            x-init="if ($el.complete && $el.naturalWidth > 0) ready = true"
            class="w-full max-h-[700px] object-contain" alt="Attachment Preview">
    @endif
</div>
