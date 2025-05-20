<style>
/* Your custom form style */
.custom-form-container {
  max-width: 800px;
  background-color: #ffffff;
  border-radius: 1rem;
  padding: 2rem;
  margin: 1rem auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.custom-form-container h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 1rem;
}

.custom-form-container label {
  color: #4a5568;
  font-weight: 500;
  margin-bottom: 0.5rem;
  display: block;
}

.custom-button {
  background-color: #2563eb;
  color: white;
  padding: 0.6rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: background-color 0.2s ease;
}

.custom-button:hover {
  background-color: #0000FF;
}

@media (max-width: 740px) {
  .form-grid {
    flex-direction: column;
  }
}
/* Uiverse.io card and avatar styles – not removed */
.card {
  --p: 32px;
  --h-form: auto;
  --w-form: 1000px;
  --input-px: 0.75rem;
  --input-py: 0.65rem;
  --submit-h: 38px;
  --blind-w: 64px;
  --space-y: 0.5rem;
  width: var(--w-form);
  height: var(--h-form);
  max-width: 100%;
  border-radius: 16px;
  background: white;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-evenly;
  flex-direction: column;
  overflow-y: auto;
  padding: var(--p);
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
  -webkit-font-smoothing: antialiased;
  -webkit-user-select: none;
  user-select: none;
  font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
    "Lucida Sans", Arial, sans-serif;
}
</style>

<div class="container mx-auto px-4">
  <div class="card">
    <div class="custom-form-container">
      <h1 class="text-2xl font-bold mb-4">Create New Office</h1>
      <form wire:submit.prevent="submitDocument" class="mt-6 space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

        <div class="flex flex-col sm:flex-row gap-4 form-grid">
          <div class="flex-1">
            <flux:input wire:model="abbreviation" :label="__('Abbreviation')" type="text" required autocomplete="abbreviation" />
          </div>
          <div class="flex-1 space-y-1.5">
            <label class="block text-sm font-medium text-gray-700">
              {{ __('Type of Office') }}
            </label>

            <flux:select wire:model="office_type" placeholder="Choose office type...">
              <flux:select.option value="ACAD">Academic</flux:select.option>
              <flux:select.option value="ADMIN">Administration</flux:select.option>
            </flux:select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ __('Head of Office') }}
          </label>

          <flux:select wire:model="office_head" placeholder="Choose office head...">
            @foreach ($users as $user)
              <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
            @endforeach
          </flux:select>
        </div>

        <div class="mt-4 flex justify-end">
          <button type="button" wire:click.prevent="saveOffice" class="custom-button">
            Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
