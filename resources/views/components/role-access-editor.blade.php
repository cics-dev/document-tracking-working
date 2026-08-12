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
                    <input type="checkbox" value="{{ $type->id }}" wire:model="{{ $documentTypesModel }}" class="mt-1">
                    <span>{{ $type->name }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
