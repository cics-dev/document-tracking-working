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
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->enum('step_type', ['routing', 'signatory', 'action'])->default('routing');
            $table->string('step_label');
            $table->string('signatory_name')->nullable();
            $table->string('signatory_position')->nullable();
            $table->string('signature_path')->nullable();
            $table->boolean('signed_for')->default(false);
            $table->integer('sequence')->default(0);
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Reviewed', 'Returned'])->default('Pending');
            $table->text('comments')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['document_id', 'status', 'sequence', 'id']);
            $table->index(['user_id', 'status']);
            $table->index(['assigned_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_steps');
    }
};
