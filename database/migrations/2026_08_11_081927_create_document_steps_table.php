<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('step_type', ['routing', 'signatory', 'action'])->default('routing');
            $table->string('step_label');
            $table->integer('sequence')->default(0);
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Reviewed', 'Returned'])->default('Pending');
            $table->text('comments')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_steps');
    }
};