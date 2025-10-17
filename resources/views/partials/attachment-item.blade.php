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

@if ($attachment->attachmentDocument && $attachment->attachmentDocument->attachments->count() > 0)
        @foreach ($attachment->attachmentDocument->attachments as $subAttachment)
            @include('partials.attachment-item', ['attachment' => $subAttachment, 'level' => $level + 1])
        @endforeach
@endif
