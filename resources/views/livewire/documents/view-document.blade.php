<div>
    <h2 class="text-lg font-bold mb-4">Document Preview</h2>

    @php
        $slips = $document->routings->whereNotNull('reviewed_at')->sortByDesc('reviewed_at');
    @endphp

    <div class="flex flex-wrap gap-4">
        @foreach($slips as $slip)
        <div x-data="{ open: false }">
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-900 p-4 mb-4 rounded shadow text-sm w-[300px] h-[100px] relative">
                <strong>Routing Slip from: {{ $slip->user->office->name }}</strong><br>
                <strong>Remarks:</strong>
                {{ $slip->comments }}

                <button @click="open = true" class="absolute top-2 right-2 text-yellow-700 hover:text-yellow-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c0 3.866-4.03 7-9 7s-9-3.134-9-7 4.03-7 9-7 9 3.134 9 7z" />
                    </svg>
                </button>
            </div>

            <div x-show="open" class="fixed inset-0 flex items-start justify-center p-4 z-50" style="display: none;">
                <div @click.away="open = false">
                    <x-routing-slip 
                        recipient="{{ $slip->user->office->name }}"
                        remarks="{{ $slip->comments }}"
                        head="{{ $slip->user->name }}"
                        date="{{ $slip->reviewed_at }}"
                    />
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if ($previewUrl)
        <iframe src="{{ $previewUrl }}" class="w-full h-[800px] border rounded" frameborder="0"></iframe>
    @else
        <p>Loading preview...</p>
    @endif
    {{-- @if($document->document_type_id == 2 && auth()->user()->position == 'University President')
        <div class="mt-4 text-lg font-semibold">
            You already signed this document
        </div> --}}
    @if($office_name != 'Administration' && $office_name != 'Records Section')
        @if(is_null($signed) && is_null($rejected))
            <div class="mt-4 flex gap-4">
                <button wire:click="sign" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">{{ $mySignatory != null || ($document->document_type_id == 2 && auth()->user()->position == 'University President') ? 'Sign' : 'Set as reviewed' }}</button>
                <button wire:click="reject" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">{{ $mySignatory != null || ($document->document_type_id == 2 && auth()->user()->position == 'University President') ? 'Reject' : 'Return with remarks' }}</button>
            </div>
        @else
            <div class="mt-4 text-lg font-semibold">
                {{ $display_text }}
            </div>
        @endif
    @elseif ($office_name == 'Administration')
        <div class="mt-4 flex gap-4">
            <button wire:click="generate" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Generate IOM</button>
            {{-- <button wire:click="generate" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">{{ $document->status == 'pending'?'Generate IOM':'View IOM' }}</button> --}}
        </div>
    @elseif ($office_name == 'Records Section' && $document->document_type_id == 2)
        <div class="mt-4 flex gap-4">
            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Send out IOM</button>
            {{-- <button wire:click="generate" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">{{ $document->status == 'pending'?'Generate IOM':'View IOM' }}</button> --}}
        </div>
    @endif
</div>
