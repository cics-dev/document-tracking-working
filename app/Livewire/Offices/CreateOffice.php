<?php

namespace App\Livewire\Offices;

use App\Http\Controllers\OfficeController;
use App\Models\Office;
use App\Models\User;
use App\Models\Role;
use App\Services\UserAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
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

    public $acting_head = '';

    public $office_id = null;

    public $edit_mode = false;

    public bool $can_manage_details = false;

    public array $newUser = [
        'family_name' => '', 'given_name' => '', 'middle_name' => '', 'middle_initial' => '',
        'suffix' => '', 'honorifics' => '', 'titles' => '', 'gender' => '', 'email' => '',
        'office_id' => '', 'position' => '', 'role_id' => '', 'is_head' => false, 'signature' => null,
    ];

    public function mount($id = null)
    {
        $this->can_manage_details = auth()->user()?->hasAccess('manage_offices') ?? false;
        $this->users = User::orderBy('name')->get();

        if ($id) {
            $office = Office::findOrFail($id);
            abort_unless($this->can_manage_details || $office->head_id === auth()->id(), 403);

            $this->office_id = $id;
            $this->name = $office->name;
            $this->abbreviation = $office->abbreviation;
            $this->office_type = $office->office_type;
            $this->office_head = $office->head_id;
            $this->acting_head = $office->acting_head_id??'';

            $this->edit_mode = true;
        } else {
            abort_unless($this->can_manage_details, 403);
        }
    }

    public function cancel()
    {
        return redirect()->route('offices.list-offices');
    }

    public function removeActingHead(): void
    {
        // if (! $this->edit_mode || ! $this->office_id) {
        //     $this->acting_head = '';

        //     return;
        // }

        // Office::findOrFail($this->office_id)->update(['acting_head_id' => null]);
        $this->acting_head = '';
    }

    public function render()
    {
        return view('livewire.offices.create-office', [
            'roles' => Role::orderBy('role')->get(),
            'offices' => Office::orderBy('name')->get(),
        ])->layout('layouts.app');
    }

    public function saveOffice()
    {
        if ($this->edit_mode && ! $this->can_manage_details) {
            $office = Office::findOrFail($this->office_id);
            abort_unless($office->head_id === auth()->id(), 403);
            $office->update(['acting_head_id' => $this->acting_head ?: null]);
            return redirect()->route('dashboard');
        }

        abort_unless($this->can_manage_details, 403);
        $creatingHead = $this->office_head === '__new_user__';
        $creatingOic = $this->acting_head === '__new_user__';
        if ($creatingHead && $creatingOic) {
            $this->addError('newUser', 'Create one account at a time. Select an existing user for either Head or OIC.');
            return;
        }

        if ($creatingHead || $creatingOic) {
            $this->validate(app(UserAccountService::class)->rules(null, false, 'newUser.'), [
                'newUser.*.required' => 'Required',
                'newUser.role_id.required' => 'Please assign a role to this user.',
            ]);
        }

        $imagePath = null;
        if ($this->office_logo) {
            $imagePath = $this->office_logo->store('office_images', 'public');
        }

        $data = [
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'office_type' => $this->office_type ?? '',
            'head_id' => $creatingHead ? null : ($this->office_head ?: null),
            'acting_head_id' => $creatingOic ? null : ($this->acting_head ?: null),
        ];

        if ($imagePath) {
            $data['office_logo'] = $imagePath;
        }

        $request = new Request($data);

        DB::transaction(function () use ($request, $creatingHead, $creatingOic): void {
            $office = $this->edit_mode
                ? tap(Office::findOrFail($this->office_id), fn ($office) => app(OfficeController::class)->update($request, $office))
                : app(OfficeController::class)->store($request);

            if ($creatingHead || $creatingOic) {
                $newUser = app(UserAccountService::class)->save($this->newUser, null, $office->id);
                $office->update([$creatingHead ? 'head_id' : 'acting_head_id' => $newUser->id]);
            }
        });

        redirect()->route('offices.list-offices');
    }
}
