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

        $this->assertSame(13, User::count());
        $this->assertSame(11, Office::count());
        $this->assertSame(7, Role::count());
        $this->assertSame(10, Permission::count());
        $this->assertSame(6, DocumentType::count());
        $this->assertSame(14, DocumentFlowStage::count());
        $this->assertSame(0, Document::count());
        $this->assertSame('System Administrator', User::findOrFail(1)->name);
        $this->assertTrue(User::findOrFail(1)->hasAccess('manage_offices'));

        $this->seed(DatabaseSeeder::class);
        $this->assertSame(13, User::count());
        $this->assertSame(0, Document::count());
    }
}
