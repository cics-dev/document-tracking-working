<div class="border rounded-xl p-4 shadow-sm bg-white hover:shadow-md transition-shadow duration-300">
    @php
        $attachmentSlips = collect();
        if ($attachment->type =='internal') {
            if ($attachment->attachmentDocument && $attachment->attachmentDocument->routings) {
                $attachmentSlips = $attachment->attachmentDocument->routings
                    ->filter(fn($routing) => $routing->reviewed_at !== null || $routing->returned_at !== null)
                    ->sortByDesc('updated_at');
            }
        }
    @endphp

    {{-- Attachment Header --}}
    <div class="flex items-center gap-3">
        <div class="p-3 rounded-lg bg-blue-50 shrink-0">
            @if ($attachment->file_type == 'pdf')
                <flux:icon.document class="w-6 h-6 text-blue-600" />
            @else
                <flux:icon.photo class="w-6 h-6 text-emerald-600" />
            @endif
        </div>

        <div class="flex items-center justify-between flex-1 min-w-0">
            <p class="font-semibold text-sm text-gray-800 truncate">
                {{ $attachment->name }}
            </p>
            <button type="button"
                class="ml-3 text-gray-500 hover:text-red-600 transition-colors duration-200 shrink-0"
                wire:click="viewAttachment('{{ $attachment->id }}', '{{ $attachment->type }}')">
                <flux:icon.eye class="w-5 h-5" />
            </button>
        </div>
    </div>

    {{-- Routing slips section (if any) --}}
    @if ($attachmentSlips->count() > 0)
        <div class="mt-4 border-t pt-3">
            <h4 class="font-semibold text-gray-700 text-sm mb-2">
                Routing Slips
            </h4>

            <div class="flex flex-wrap gap-4">
                @foreach ($attachmentSlips as $slip)
                    <div x-data="{ open: false }" class="relative">
                        <div
                            class="bg-blue-100 border-l-4 border-blue-500 text-blue-900 p-4 rounded shadow text-sm w-[280px] h-[120px] relative">
                            <strong>From:</strong> {{ $slip->user->office->abbreviation }}<br>
                            <strong>Status:</strong> {{ $slip->returned_at ? 'Returned with remarks' : 'Reviewed' }}<br>
                            <strong>Remarks:</strong> 
                            <p class="truncate w-[260px]">
                                {{ $slip->comments }}
                            </p>

                            <button @click="open = true"
                                class="absolute top-2 right-2 text-blue-700 hover:text-blue-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c0 3.866-4.03 7-9 7s-9-3.134-9-7 4.03-7 9-7 9 3.134 9 7z" />
                                </svg>
                            </button>
                        </div>

                        {{-- Modal view --}}
                        <div 
                                x-show="open"
                                class="fixed inset-0 flex items-start justify-center p-4 z-50"
                                style="display: none;"
                            >
                            <div @click.away="open = false" class="bg-white rounded-lg shadow-lg p-6 max-w-lg">
                                <x-routing-slip
                                    recipient="{{ $slip->user->office->name }}"
                                    remarks="{{ $slip->comments }}"
                                    head="{{ $slip->user->name }}"
                                    date="{{ $slip->reviewed_at ?? $slip->returned_at }}"
                                />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@if ($attachment->type =='internal' && $attachment->attachmentDocument && $attachment->attachmentDocument->all_attachments->count() > 0)
        @foreach ($attachment->attachmentDocument->all_attachments as $subAttachment)
            @include('partials.attachment-item', ['attachment' => $subAttachment, 'level' => $level + 1])
        @endforeach
@endif
