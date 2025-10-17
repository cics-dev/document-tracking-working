<div>
    <h2 class="text-lg font-bold mb-4">Document Preview</h2>
    @if ($previewUrl)
        <iframe src="{{ $previewUrl }}" class="w-full h-[800px] border rounded" frameborder="0"></iframe>
    @else
        <p>Loading preview...</p>
    @endif
</div>
