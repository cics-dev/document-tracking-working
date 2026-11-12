<?php

use App\Livewire\Offices\CreateOffice;
use App\Livewire\Settings\Office as OfficeSettings;
use App\Models\{Office, Permission, Role, User};
use App\Support\DocumentNumber;
use Livewire\Livewire;

it('allows two offices to share SAO through creation and editing', function () {
    $role = Role::create(['role' => 'office-abbreviation-admin', 'description' => 'Office administrator']);
    $role->permissions()->attach(Permission::where('key', 'manage_offices')->firstOrFail());
    $this->actingAs(User::factory()->create(['role_id' => $role->id]));
    Office::create(['name' => 'SAO Administration', 'abbreviation' => 'SAO', 'document_number_tag' => 'ADMIN', 'office_type' => 'ADMIN']);
    Livewire::test(CreateOffice::class)->set('name', 'SAO Finance')->set('abbreviation', 'SAO')
        ->set('document_number_tag', 'FINANCE')->set('office_type', 'ADMIN')->call('saveOffice')->assertHasNoErrors();
    $finance = Office::where('name', 'SAO Finance')->firstOrFail();
    expect(Office::where('abbreviation', 'SAO')->count())->toBe(2);
    $finance->update(['abbreviation' => 'SAO-F']);
    Livewire::test(CreateOffice::class, ['id' => $finance->id])->set('abbreviation', 'SAO')->call('saveOffice')->assertHasNoErrors();
    expect($finance->fresh()->abbreviation)->toBe('SAO');
    expect(DocumentNumber::format(null, $finance->fresh(), 1, 2026))->toStartWith('SAO(FINANCE)-');
});

it('allows an office head to use a shared abbreviation in office settings', function () {
    Office::create(['name' => 'SAO Administration', 'abbreviation' => 'SAO', 'document_number_tag' => 'ADMIN', 'office_type' => 'ADMIN']);
    $office = Office::create(['name' => 'SAO Finance', 'abbreviation' => 'SAO-F', 'document_number_tag' => 'FINANCE', 'office_type' => 'ADMIN']);
    $head = User::factory()->create(['office_id' => $office->id]);
    $office->update(['head_id' => $head->id]);
    $this->actingAs($head);
    Livewire::test(OfficeSettings::class)->set('abbreviation', 'SAO')->call('save')->assertHasNoErrors();
    expect($office->fresh()->abbreviation)->toBe('SAO');
});
