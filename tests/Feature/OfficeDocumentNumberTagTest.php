<?php

use App\Http\Controllers\OfficeController;
use App\Livewire\Settings\Office as OfficeSettings;
use App\Models\Office;
use App\Models\User;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('document number tags are independent of office type and may be omitted', function () {
    $office = Office::create(['name' => 'Technology', 'abbreviation' => 'ICTU', 'office_type' => 'ADMIN', 'document_number_tag' => 'FINANCE']);

    expect(DocumentNumber::format(null, $office, 1, 2026))->toBe('ICTU(FINANCE)--1-2026');
    expect(preg_match(DocumentNumber::pattern(null, $office), 'ICTU(FINANCE)--1-2026'))->toBe(1);

    $office->update(['document_number_tag' => null]);
    expect(DocumentNumber::format(null, $office, 1, 2026))->toBe('ICTU--1-2026');
    expect($office->office_type)->toBe('ADMIN');
});

test('recipient filtering still uses office type regardless of document number tag', function () {
    $admin = Office::create(['name' => 'Admin', 'abbreviation' => 'AD', 'office_type' => 'ADMIN']);
    $acad = Office::create(['name' => 'Academic', 'abbreviation' => 'AC', 'office_type' => 'ACAD', 'document_number_tag' => 'ADMIN']);
    $controller = app(OfficeController::class);

    expect($controller->index('ADMIN', false)->modelKeys())->toContain($admin->id, $acad->id);
    expect($controller->index('ACAD', false)->modelKeys())->toBe([$admin->id]);
    expect($controller->index('', false)->modelKeys())->toBe([$admin->id]);
});

test('office head can set a custom document number tag and clear it', function () {
    $office = Office::create(['name' => 'Technology', 'abbreviation' => 'ICTU', 'office_type' => 'ADMIN']);
    $head = User::factory()->create(['office_id' => $office->id]);
    $office->update(['head_id' => $head->id]);
    $this->actingAs($head);

    Livewire::test(OfficeSettings::class)
        ->set('document_number_tag', 'FINANCE')
        ->call('save')->assertHasNoErrors();
    expect($office->fresh()->document_number_tag)->toBe('FINANCE');

    Livewire::test(OfficeSettings::class)
        ->set('document_number_tag', '')
        ->call('save')->assertHasNoErrors();
    expect(DocumentNumber::format(null, $office->fresh(), 1, 2026))->toBe('ICTU--1-2026');
    expect($office->fresh()->office_type)->toBe('ADMIN');
});

test('migration preserves numbering tags for existing and archived offices', function () {
    $migration = require database_path('migrations/2026_11_12_000001_add_document_number_tag_to_offices_table.php');
    $migration->down();
    $admin = DB::table('offices')->insertGetId(['name' => 'Admin', 'abbreviation' => 'AD', 'office_type' => 'ADMIN']);
    $acad = DB::table('offices')->insertGetId(['name' => 'Academic', 'abbreviation' => 'AC', 'office_type' => 'ACAD', 'deleted_at' => now()]);
    $blank = DB::table('offices')->insertGetId(['name' => 'Blank', 'abbreviation' => 'BL', 'office_type' => '']);

    $migration->up();

    expect(DB::table('offices')->where('id', $admin)->value('document_number_tag'))->toBe('ADMIN');
    expect(DB::table('offices')->where('id', $acad)->value('document_number_tag'))->toBe('ACAD');
    expect(DB::table('offices')->where('id', $blank)->value('document_number_tag'))->toBeNull();
});
