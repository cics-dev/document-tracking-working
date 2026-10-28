<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('adds nullable auth columns while preserving existing user records', function () {
    $migration = require database_path('migrations/2026_10_29_000001_add_authentication_columns_to_users_table.php');
    $migration->down();
    $id = DB::table('users')->insertGetId([
        'name' => 'Existing User', 'email' => 'existing@example.test', 'password' => 'unchanged-hash',
    ]);

    $migration->up();

    $user = DB::table('users')->find($id);
    expect($user->name)->toBe('Existing User');
    expect($user->password)->toBe('unchanged-hash');
    expect($user->email_verified_at)->toBeNull();
    expect($user->remember_token)->toBeNull();
    expect(Schema::hasColumns('users', ['email_verified_at', 'remember_token']))->toBeTrue();
});

it('keeps dashboard access available without email verification', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get('/dashboard')->assertOk();
    expect($user->fresh()->email_verified_at)->toBeNull();
});
