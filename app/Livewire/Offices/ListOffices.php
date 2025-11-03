<?php

namespace App\Livewire\Offices;

use App\Http\Controllers\OfficeController;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Livewire\WithPagination;

class ListOffices extends Component
{
    use WithPagination;

    public $name, $abbreviation, $office_type, $head_id;
    public $editMode = false;
    public $officeId;
    public $content = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'abbreviation' => 'required|string|max:50|unique:offices,abbreviation',
        'office_type' => 'required|in:ACAD,ADMIN',
        'head_id' => 'nullable|exists:users,id',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function saveOffice()
    {
        $this->validate();

        Http::post(config('app.url') . '/api/offices', [
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'office_type' => $this->office_type,
            'head_id' => $this->head_id,
        ]);

        $this->resetForm();
        $this->fetchOffices();
    }

    public function editOffice($id)
    {
        $offices = app(OfficeController::class)->index('ADMIN', false);
        $office = collect($offices)->firstWhere('id', $id);
        $this->officeId = $id;
        $this->name = $office['name'];
        $this->abbreviation = $office['abbreviation'];
        $this->office_type = $office['office_type'];
        $this->head_id = $office['head_id'];
        
        return view('livewire.offices.edit-office');
    }

    public function updateOffice()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:50|unique:offices,abbreviation,' . $this->officeId,
            'office_type' => 'required|in:ACAD,ADMIN',
            'head_id' => 'nullable|exists:users,id',
        ]);

        Http::put(config('app.url') . '/api/offices/' . $this->officeId, [
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'office_type' => $this->office_type,
            'head_id' => $this->head_id,
        ]);

        $this->resetForm();
        $this->fetchOffices();
    }

    public function deleteOffice($id)
    {
        Http::delete(config('app.url') . '/api/offices/' . $id);
        $this->fetchOffices();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->abbreviation = '';
        $this->office_type = '';
        $this->head_id = '';
        $this->editMode = false;
        $this->officeId = null;
    }

    public function render()
    {
        return view('livewire.offices.list-offices', ['offices'=>app(OfficeController::class)->index('ADMIN', true)]);
    }
}