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

        // An UPDATE joined to users works on MySQL, but Laravel compiles it to an
        // invalid SQLite statement. Backfill row by row instead so this migration
        // remains portable (including the in-memory SQLite test database).
        $playerNames = DB::table('users')->pluck('name', 'id');

        DB::table('tetris_scores')
            ->whereNull('player_name')
            ->orderBy('id')
            ->each(function (object $score) use ($playerNames): void {
                $playerName = $playerNames->get($score->user_id);

                if ($playerName !== null) {
                    DB::table('tetris_scores')
                        ->where('id', $score->id)
                        ->update(['player_name' => $playerName]);
                }
            });

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
