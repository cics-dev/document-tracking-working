<?php

namespace App\Livewire\Offices;

use App\Http\Controllers\OfficeController;
use App\Models\User;
use App\Models\Office;
use Livewire\Component;
use Illuminate\Http\Request;
use Livewire\WithFileUploads;

class CreateOffice extends Component
{
    use WithFileUploads;

    public $users = [];
    public $name = '';
    public $office_logo;
    public $abbreviation = '';
    public $office_type = '';
    public $office_head = '';
    public $office_id = null;
    public $edit_mode = false;

    public function mount($id = null)
    {
        $this->users = User::all();

        if ($id) {
            $office = Office::findOrFail($id);

            $this->office_id = $id;
            $this->name = $office->name;
            $this->abbreviation = $office->abbreviation;
            $this->office_type = $office->office_type;
            $this->office_head = $office->head_id;

            $this->edit_mode = true;
        }
    }

    public function cancel()
    {
        return redirect()->route('offices.list-offices');
    }

    public function render()
    {
        return view('livewire.offices.create-office')->layout('layouts.app');
    }

    public function saveOffice()
    {
        $imagePath = null;
        if ($this->office_logo) {
            $imagePath = $this->office_logo->store('office_images', 'public');
        }

        $data = [
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'office_type' => $this->office_type ?? '',
            'head_id' => $this->office_head ?: null,
        ];

        if ($imagePath) {
            $data['office_logo'] = $imagePath;
        }

        $request = new Request($data);

        if ($this->edit_mode) {
            $office = Office::findOrFail($this->office_id);
            app(OfficeController::class)->update($request, $office);
        } else {
            app(OfficeController::class)->store($request);
        }

        redirect()->route('offices.list-offices');
    }
}