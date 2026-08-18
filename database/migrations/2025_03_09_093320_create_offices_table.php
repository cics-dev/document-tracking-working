<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abbreviation', 50)->unique();
            $table->enum('office_type', ['ACAD', 'ADMIN', ''])->default('');
            $table->unsignedBigInteger('head_id')->nullable();
            $table->unsignedBigInteger('acting_head_id')->nullable();
            $table->string('workflow_key')->nullable()->unique();
            $table->string('office_logo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
