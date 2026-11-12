<div x-data="{ documentReady: false }">
    <div class="mb-4 flex items-center gap-2">
        <h2 class="text-lg font-bold">Document Preview</h2>
        <flux:badge size="sm" :color="$document->is_physical ? 'amber' : 'zinc'">{{ $document->is_physical ? 'Physical' : 'Digital' }}</flux:badge>
    </div>

    @if ($document->is_physical)
        <div class="mb-4 space-y-1">
            <flux:heading>{{ $document->subject }}</flux:heading>
            <flux:text>{{ $document->document_number }} · Archived</flux:text>
            @if ($document->date_sent)
                <flux:text>Signed on paper {{ $document->date_sent->format('M d, Y') }}</flux:text>
            @endif
            @if ($document->cfs->isNotEmpty())
                <flux:text>CF recipients: {{ $document->cfs->pluck('user.name')->filter()->join(', ') }}</flux:text>
            @endif
        </div>
    @endif

    <fieldset disabled x-bind:disabled="!documentReady" class="min-w-0 border-0 p-0">

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
                <strong>Status: {{ $slip->status === 'Returned' ? 'Returned'.(filled($slip->comments) ? ' with remarks' : '') : 'Reviewed' }}</strong><br>
                @if(filled($slip->comments))
                    <strong>Remarks:</strong>
                    <p class="truncate w-[260px]">{{ $slip->comments }}</p>
                @endif

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
        <div x-show="!documentReady" class="mb-3 rounded border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            Loading document data and PDF&hellip; Actions will be available when loading is complete.
        </div>
        <iframe
            src="{{ $previewUrl }}"
            class="w-full h-[800px] border rounded"
            frameborder="0"
            title="{{ $document->is_physical ? 'Signed paper document scan' : 'Document PDF preview' }}"
            @load="documentReady = true"
        ></iframe>
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
    @if($oicViewOnly)
        <div role="status" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            You can view this document, but only your assigned OIC can take action while acting on your behalf.
        </div>
    @elseif(
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
                @if ($signingStepId === null)
                    <flux:error name="twoFactorCode" />
                @endif
                <div class="mt-4 flex gap-4">
                    <button data-form-action wire:click="sign" wire:loading.attr="disabled" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="sign">{{ $myStep && $myStep->step_type === 'routing' ? 'Set as reviewed' : 'Sign' }}</span>
                        <span wire:loading wire:target="sign">Processing...</span>
                    </button>
                    <button data-form-action wire:click="reject" wire:loading.attr="disabled" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
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
                    <button wire:click="generateDocument({{ $rule['id'] }})" wire:loading.attr="disabled" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50">{{ $rule['button_label'] }}</button>
                @endforeach
            </div>
        @endif
    @endif

    <flux:modal name="verify-signature" class="max-w-md w-full">
        <form wire:submit="confirmSignature" class="space-y-4">
            <flux:heading size="lg">{{ $rejectingSignature ? 'Confirm rejection' : 'Confirm signature' }}</flux:heading>
            <flux:text>Enter a fresh code from your authenticator app or use a recovery code.</flux:text>
            <flux:input wire:model="signingRemarks" label="Remarks (optional)" />
            <flux:input wire:model="twoFactorCode" label="Verification code" autocomplete="one-time-code" required />
            <flux:error name="twoFactorCode" />
            <flux:error name="document" />
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" :variant="$rejectingSignature ? 'danger' : 'primary'">
                    {{ $rejectingSignature ? 'Reject document' : 'Sign document' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

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

    </fieldset>

</div>
