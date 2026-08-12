<?php

use App\Livewire\Settings\Office as OfficeSettings;
use App\Models\Office;
use App\Models\User;
use Livewire\Livewire;

test('office head can update office details and oic from settings', function () {
    $office = Office::create(['name' => 'Old Office', 'abbreviation' => 'OLD', 'office_type' => 'ADMIN']);
    $head = User::factory()->create(['office_id' => $office->id]);
    $oic = User::factory()->create(['office_id' => $office->id]);
    $office->update(['head_id' => $head->id]);

    $this->actingAs($head);

    Livewire::test(OfficeSettings::class)
        ->set('name', 'Updated Office')
        ->set('abbreviation', 'UPD')
        ->set('office_type', 'ACAD')
        ->set('acting_head', (string) $oic->id)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('offices', [
        'id' => $office->id,
        'name' => 'Updated Office',
        'abbreviation' => 'UPD',
        'office_type' => 'ACAD',
        'head_id' => $head->id,
        'acting_head_id' => $oic->id,
    ]);
});

test('non head cannot open office settings', function () {
    $office = Office::create(['name' => 'Office', 'abbreviation' => 'OFF', 'office_type' => 'ADMIN']);
    $staff = User::factory()->create(['office_id' => $office->id]);

    $this->actingAs($staff)
        ->get(route('settings.office'))
        ->assertForbidden();
});
