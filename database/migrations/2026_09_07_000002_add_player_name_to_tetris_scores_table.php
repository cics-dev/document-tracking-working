<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tetris_scores', function (Blueprint $table) {
            $table->string('player_name', 40)->nullable()->after('user_id');
            $table->index(['player_name', 'score']);
        });

        DB::table('tetris_scores')
            ->join('users', 'users.id', '=', 'tetris_scores.user_id')
            ->whereNull('tetris_scores.player_name')
            ->update(['tetris_scores.player_name' => DB::raw('users.name')]);

        Schema::table('tetris_scores', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tetris_scores', function (Blueprint $table) {
            $table->dropIndex(['player_name', 'score']);
            $table->dropColumn('player_name');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};