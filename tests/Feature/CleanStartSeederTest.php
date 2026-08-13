<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\DocumentType;
use App\Models\DocumentFlowStage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanStartSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_seed_creates_only_the_admin_user_and_no_operational_offices(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertSame('admin@example.com', User::firstOrFail()->email);
        $this->assertSame(0, Office::count());
        $this->assertSame(1, Role::count());
        $this->assertSame('admin', Role::firstOrFail()->role);
        $this->assertSame(0, DocumentType::count());
        $this->assertSame(0, DocumentFlowStage::count());
        $this->assertGreaterThan(0, Permission::count());
        $this->assertTrue(User::firstOrFail()->hasAccess('manage_offices'));
    }
}
