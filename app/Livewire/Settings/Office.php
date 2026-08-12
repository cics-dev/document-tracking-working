<?php

namespace App\Livewire\Settings;

use App\Models\Office as OfficeModel;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Office extends Component
{
    use WithFileUploads;

    public OfficeModel $office;
    public string $name = '';
    public string $abbreviation = '';
    public string $office_type = '';
    public $acting_head = '';
    public $office_logo;
    public ?string $current_logo = null;

    public function mount(): void
    {
        $office = auth()->user()?->office;
        abort_unless($office && $office->head_id === auth()->id(), 403);

        $this->office = $office;
        $this->name = $office->name;
        $this->abbreviation = $office->abbreviation;
        $this->office_type = $office->office_type;
        $this->acting_head = $office->acting_head_id ?: '';
        $this->current_logo = $office->office_logo;
    }

    public function removeActingHead(): void
    {
        $this->acting_head = '';
    }

    public function save(): void
    {
        abort_unless($this->office->fresh()?->head_id === auth()->id(), 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'abbreviation' => ['required', 'string', 'max:50', Rule::unique('offices', 'abbreviation')->ignore($this->office->id)],
            'office_type' => ['required', Rule::in(['ACAD', 'ADMIN'])],
            'acting_head' => ['nullable', 'exists:users,id'],
            'office_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        unset($data['office_logo']);
        $data['acting_head_id'] = $data['acting_head'] ?: null;
        unset($data['acting_head']);

        if ($this->office_logo) {
            $data['office_logo'] = $this->office_logo->store('office_images', 'public');
        }

        $this->office->update($data);
        $this->current_logo = $this->office->fresh()->office_logo;
        $this->reset('office_logo');
        session()->flash('status', 'Office details updated.');
    }

    public function render()
    {
        return view('livewire.settings.office', [
            'users' => User::orderBy('name')->get(['id', 'name']),
        ])->layout('layouts.app');
    }
}
