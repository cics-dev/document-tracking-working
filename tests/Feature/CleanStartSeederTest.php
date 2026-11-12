<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\DocumentType;
use App\Models\DocumentFlowStage;
use App\Models\Document;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanStartSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_seed_restores_setup_data_without_documents(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(17, User::count());
        $this->assertSame(15, Office::count());
        $hrmu = Office::where('abbreviation', 'HRMU')->sole();
        $this->assertNotNull($hrmu->head);
        $this->assertSame($hrmu->id, $hrmu->head->office_id);
        $this->assertSame(7, Role::count());
        $this->assertSame(10, Permission::count());
        $this->assertSame(6, DocumentType::count());
        $this->assertSame(24, DocumentFlowStage::count());
        $this->assertSame(0, Document::count());
        $this->assertSame('System Administrator', User::findOrFail(1)->name);
        $this->assertTrue(User::findOrFail(1)->hasAccess('manage_offices'));

        $snapshot = json_decode(file_get_contents(database_path('seeders/data/setup.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($snapshot as $table => $rows) {
            $this->assertSame(count($rows), \Illuminate\Support\Facades\DB::table($table)->count(), $table);
            foreach ($rows as $row) {
                $this->assertDatabaseHas($table, $row);
            }
        }
        foreach (['documents', 'external_documents', 'document_steps', 'document_logs', 'document_attachments', 'document_carbon_copies', 'document_access_logs', 'document_tracking_shares'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $password = \Illuminate\Support\Facades\Hash::make('keep-existing-password');
        User::findOrFail(1)->update(['password' => $password]);

        $this->seed(DatabaseSeeder::class);
        $this->assertSame(17, User::count());
        $this->assertSame(0, Document::count());
        $this->assertSame($password, User::findOrFail(1)->password);
    }
}
