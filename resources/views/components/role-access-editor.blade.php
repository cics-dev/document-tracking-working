@props([
    'permissions',
    'types',
    'rightsModel' => 'rights',
    'documentTypesModel' => 'documentTypes',
    'errorName' => 'rights',
])

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <h3 class="mb-2 text-sm font-medium">System rights</h3>
        <div class="grid gap-2">
            @foreach ($permissions as $permission)
                <label class="flex items-start gap-2 text-sm">
                    <input type="checkbox" value="{{ $permission->id }}" wire:model="{{ $rightsModel }}" class="mt-1">
                    <span>{{ $permission->label }}</span>
                </label>
            @endforeach
        </div>
        @error($errorName) <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <h3 class="mb-2 text-sm font-medium">Can create document types</h3>
        <div class="grid gap-2">
            @foreach ($types as $type)
                <label class="flex items-start gap-2 text-sm">
                    @if($type->is_publicly_creatable)
                        <input type="checkbox" checked disabled data-public-document-type="{{ $type->id }}" class="mt-1">
                    @else
                        <input type="checkbox" value="{{ $type->id }}" wire:model="{{ $documentTypesModel }}" class="mt-1">
                    @endif
                    <span>
                        {{ $type->name }}
                        @if($type->is_publicly_creatable)
                            <span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">All users</span>
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</div>
