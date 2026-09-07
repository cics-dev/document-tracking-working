<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tetris_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score');
            $table->timestamps();

            $table->index(['user_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tetris_scores');
    }
};