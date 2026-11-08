<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('require_two_factor_for_login')->default(false);
            $table->boolean('require_two_factor_for_signing')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->unsignedBigInteger('two_factor_last_used_step')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'require_two_factor_for_login',
                'require_two_factor_for_signing',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_last_used_step',
            ]);
        });
    }
};
