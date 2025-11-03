<div>
    <h2 class="text-lg font-bold mb-4">Document Preview</h2>
    {{-- @if ($previewUrl)
        <iframe src="{{ $previewUrl }}" class="w-full h-[800px] border rounded" frameborder="0"></iframe>
    @else
        <p>Loading preview...</p>
    @endif --}}
    <div class="mt-4 flex gap-4">
        <button wire:click="generateECLR" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Generate ECLR</button>
        <button wire:click="generateRLM" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Generate RLM</button>
    </div>
</div>
