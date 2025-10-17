<?php

namespace App\Livewire\Offices;

use App\Http\Controllers\OfficeController;
use App\Models\User;
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

    public function mount()
    {
        $this->users = User::all();
    }

    public function render()
    {
        return view('livewire.offices.create-office');
    }

    public function saveOffice()
    {
        $imagePath = null;
        if ($this->office_logo) {
            $imagePath = $this->office_logo->store('office_images', 'public');
        }


        $data = new Request([
            'name'=>$this->name,
            'office_logo'=>$imagePath,
            'abbreviation'=>$this->abbreviation,
            'office_type'=>$this->office_type,
            'head_id'=>$this->office_head == ''?null:$this->office_head
        ]);
        app(OfficeController::class)->store($data);
        redirect()->route('offices.list-offices');
    }
}