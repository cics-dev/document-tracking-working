<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_and_offices_are_archived_and_hidden_from_normal_lists(): void
    {
        $office = Office::create(['name' => 'Historical Office', 'abbreviation' => 'HO', 'office_type' => 'ADMIN']);
        $user = User::factory()->create(['office_id' => $office->id]);

        $user->delete();
        $office->delete();

        $this->assertNull(User::find($user->id));
        $this->assertNull(Office::find($office->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
        $this->assertNotNull(Office::withTrashed()->find($office->id));
    }
}
