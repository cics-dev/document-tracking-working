<div>
    <h2 class="text-lg font-bold mb-4">Document Preview</h2>
    @if ($previewUrl && $previewType === 'pdf')
        <iframe src="{{ $previewUrl }}" class="w-full h-[800px] border rounded" frameborder="0"></iframe>
    @elseif($previewUrl && $previewType === 'image')
        <div class="flex justify-center rounded border bg-gray-50 p-4">
            <img src="{{ $previewUrl }}" alt="External document attachment" class="max-h-[800px] max-w-full object-contain">
        </div>
    @else
        <div class="rounded border border-amber-200 bg-amber-50 p-4 text-amber-800">
            This attachment cannot be previewed in the browser. It will not be downloaded automatically.
        </div>
    @endif
    @if(!empty($generationRules))
        @if($document->document_id == null)
            <div class="mt-4 flex gap-4">
                @foreach($generationRules as $rule)<button wire:click="generateDocument({{ $rule['id'] }})" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">{{ $rule['button_label'] }}</button>@endforeach
            </div>
        @else
            <div class="mt-4 text-lg font-semibold">
                You've already generated document from this external communication letter
            </div>
        @endif
    @endif
</div>
