<div>
    <h2 class="text-lg font-bold mb-4">Document Preview</h2>

    @if ($previewUrl)
        <iframe src="{{ $previewUrl }}" class="w-full h-[800px] border rounded" frameborder="0"></iframe>
    @else
        <p>Loading preview...</p>
    @endif

    @if($office_name != 'Administration' && $office_name != 'Records Section')
        @if(is_null($signed) && is_null($rejected))
            <div class="mt-4 flex gap-4">
                <button wire:click="sign" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Sign</button>
                <button wire:click="reject" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Reject</button>
            </div>
        @else
            <div class="mt-4 text-lg font-semibold">
                {{ $display_text }}
            </div>
        @endif
    @elseif ($office_name == 'Administration')
        <div class="mt-4 flex gap-4">
            <button wire:click="generate" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Generate IOM</button>
        </div>
    @endif
</div>
