<?php

namespace App\Livewire\Offices;

use App\Http\Controllers\OfficeController;
use App\Models\User;
<<<<<<< HEAD
use App\Models\Office;
=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
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
<<<<<<< HEAD
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
=======

    public function mount()
    {
        $this->users = User::all();
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    }

    public function render()
    {
<<<<<<< HEAD
        return view('livewire.offices.create-office')->layout('layouts.app');
=======
        return view('livewire.offices.create-office');
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    }

    public function saveOffice()
    {
        $imagePath = null;
        if ($this->office_logo) {
            $imagePath = $this->office_logo->store('office_images', 'public');
        }

<<<<<<< HEAD
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

=======
        $data = new Request([
            'name'=>$this->name,
            'office_logo'=>$imagePath,
            'abbreviation'=>$this->abbreviation,
            'office_type'=>$this->office_type,
            'head_id'=>$this->office_head == ''?null:$this->office_head
        ]);
        app(OfficeController::class)->store($data);
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
        redirect()->route('offices.list-offices');
    }
}