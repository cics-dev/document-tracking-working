<div>
    <h2 class="text-lg font-bold mb-4">Document Preview</h2>

    @php
        $slips = collect();
        if ($document && $document->steps) {
            $slips = $document->steps
                ->filter(fn($step) => $step->step_type === 'routing' && !empty($step->processed_at))
                ->sortByDesc('updated_at');
        }
    @endphp

    <div class="flex flex-wrap gap-4">
        @foreach($slips as $slip)
        <div x-data="{ open: false }">
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-900 p-4 mb-4 rounded shadow text-sm w-[300px] h-[120px] relative">
                <strong>Routing Slip from: {{ $slip->office?->abbreviation ?? $slip->user->office->abbreviation }}</strong><br>
                <strong>Status: {{ $slip->status === 'Returned' ? 'Returned with remarks' : 'Reviewed' }}</strong><br>
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
                        recipient="{{ $slip->office?->name ?? $slip->user->office->name }}"
                        remarks="{{ $slip->comments }}"
                        head="{{ $slip->signatory_name ?? $slip->office?->head?->name ?? $slip->user->name }}"
                        date="{{ $slip->processed_at }}"
                        signature="{{ $slip->signature_path }}"
                        :signed-for="$slip->signed_for"
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
    @if(
        (
            $document->document_level != 'Intra' &&
            ($document['from_id'] != auth()->user()->office->id || auth()->user()->id == 2) &&
            ($isSignatory || $isRouting)
        )
        ||
        (
            $canGenerate
        )
    )
        @if(!$canGenerate)
            @if($canAct && empty($signed) && empty($rejected))
                <div class="mt-4 flex gap-4">
                    <button wire:click="sign" wire:loading.attr="disabled" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <span wire:loading.remove wire:target="sign">{{ $myStep && $myStep->step_type === 'routing' ? 'Set as reviewed' : 'Sign' }}</span>
                        <span wire:loading wire:target="sign">Processing...</span>
                    </button>
                    <button wire:click="reject" wire:loading.attr="disabled" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        <span wire:loading.remove wire:target="reject">{{ $myStep && $myStep->step_type === 'routing' ? 'Return with remarks' : 'Reject' }}</span>
                        <span wire:loading wire:target="reject">Processing...</span>
                    </button>
                </div>
            @else
                <div class="mt-4 text-lg font-semibold">
                    {{ $display_text }}
                </div>
            @endif
        @elseif ($canGenerate)
            <div class="mt-4 flex flex-wrap gap-4">
                @foreach($generationRules as $rule)
                    <button wire:click="generateDocument({{ $rule['id'] }})" wire:loading.attr="disabled" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">{{ $rule['button_label'] }}</button>
                @endforeach
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
                    @if ($attachmentPreviewType === 'pdf')
                        @if ($attachmentPreviewUrl)
                            <iframe src="{{ $attachmentPreviewUrl }}" class="w-full h-[600px] border rounded" frameborder="0"></iframe>
                        @else
                            <p>Loading preview...</p>
                        @endif
                    @elseif($attachmentPreviewType === 'image')
                        <img src="{{ $attachmentPreviewUrl }}" class="w-full max-h-[700px] object-contain" alt="Attachment Preview">
                    @else
                        <p class="p-4 text-amber-700">This file type cannot be previewed.</p>
                    @endif
                </div>
            @endif
        </div>
    </flux:modal>

</div>
