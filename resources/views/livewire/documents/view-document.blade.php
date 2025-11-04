<div>
    <h2 class="text-lg font-bold mb-4">Document Preview</h2>

    @php
        $slips = collect();
        if ($document && $document->routings) {
            $slips = $document->routings
                ->filter(fn($routing) => $routing->reviewed_at !== null || $routing->returned_at !== null)
                ->sortByDesc('updated_at');
        }
    @endphp

    <div class="flex flex-wrap gap-4">
        @foreach($slips as $slip)
        <div x-data="{ open: false }">
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-900 p-4 mb-4 rounded shadow text-sm w-[300px] h-[100px] relative">
                <strong>Routing Slip from: {{ $slip->user->office->name }}</strong><br>
                <strong>Status: {{ $slip->returned_at?'Returned with remarks':'Reviewed' }}</strong><br>
                <strong>Remarks:</strong>
                <p class="truncate w-[260px]">
                    {{ $slip->comments }}
                </p>

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
    @if ($document->all_attachments && $document->all_attachments->count() > 0)
        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-3">Attachments</h3>
            <div class="space-y-4">
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-start">
                    @foreach ($document->all_attachments as $attachment)
                        @include('partials.attachment-item', ['attachment' => $attachment, 'level' => 0])
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if ($document->attachments && $document->attachments->count() > 0)
        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-3">Attachments</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($document->attachments as $attachment)
                    <div class="border rounded-xl p-4 shadow-sm bg-white hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-3 rounded-lg bg-blue-50">
                                @if ($attachment->file_type == 'pdf')
                                    <flux:icon.document class="w-6 h-6 text-blue-600"/>
                                @else
                                    <flux:icon.photo class="w-6 h-6 text-emerald-600"/>
                                @endif
                            </div>
                            
                            <div class="flex items-center justify-between flex-1 min-w-0">
                                <p class="font-semibold text-sm text-gray-800 truncate">
                                    {{ $attachment->name }}
                                </p>
                                <button type="button"
                                        class="ml-3 text-gray-500 hover:text-red-600 transition-colors duration-200 shrink-0"
                                        wire:click="viewAttachment('{{ $attachment->id }}')">
                                    <flux:icon.eye class="w-5 h-5"/>
                                </button>
                            </div>

                        </div>
                    </div>

                @endforeach
            </div>
        </div>
    @endif
    
    @if(
        (
            $document->document_level != 'Intra' &&
            ($document['from_id'] != auth()->user()->office->id || auth()->user()->id == 2) &&
            ($isSignatory || $isRouting)
        )
        ||
        (
            auth()->user()->office->name == 'Administration' &&
            $document->document_type_id == 1
        )
    )
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
            @if ($document->status == 'pending' || $document->status == 'sent')
                <div class="mt-4 flex gap-4">
                    <button wire:click="generate" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Generate IOM</button>
                    {{-- <button wire:click="generate" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">{{ $document->status == 'pending'?'Generate IOM':'View IOM' }}</button> --}}
                </div>
            @else
                <div class="mt-4 text-lg font-semibold">
                    You've already generated IOM
                </div>
            @endif
        @elseif ($office_name == 'Records Section' && $document->document_type_id == 2)
            <div class="mt-4 flex gap-4">
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Send out IOM</button>
                {{-- <button wire:click="generate" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">{{ $document->status == 'pending'?'Generate IOM':'View IOM' }}</button> --}}
            </div>
        @endif
    @endif

    <flux:modal name="view-attachment-modal" class="max-w-5xl w-full">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Attachment Preview</flux:heading>
            </div>
            @if ($selectedAttachment)
                <div class="border rounded-lg overflow-hidden">
                    @if ($selectedAttachment->file_type == 'pdf')
                        @if ($attachmentPreviewUrl)
                            <iframe src="{{ $attachmentPreviewUrl }}" class="w-full h-[600px] border rounded" frameborder="0"></iframe>
                        @else
                            <p>Loading preview...</p>
                        @endif
                    @else
                        <img src="{{ $attachmentPreviewUrl }}" class="w-full max-h-[700px] object-contain" alt="Attachment Preview">
                    @endif
                </div>
            @endif
        </div>
    </flux:modal>

</div>
