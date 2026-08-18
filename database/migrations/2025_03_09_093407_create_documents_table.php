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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique()->nullable();
            $table->foreignId('from_id')->constrained('offices')->restrictOnDelete();
            $table->string('from_name')->nullable();
            $table->string('from_position')->nullable();
            $table->foreignId('to_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->string('to_name')->nullable();
            $table->string('to_position')->nullable();
            $table->foreignId('document_type_id')->constrained('document_types')->restrictOnDelete();
            $table->string('thru')->nullable();
            $table->string('subject');
            $table->text('content');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('Draft');
            $table->date('date_sent')->nullable();
            $table->string('file_url')->nullable();
            $table->string('document_level')->default('Inter'); // Inter, Intra, External
            $table->string('to_text')->nullable(); // Inter, Intra, External
            $table->boolean('is_revision')->nullable();
            $table->foreignId('original_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
            $table->index(['from_id', 'document_type_id', 'status']);
            $table->index(['to_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['document_level', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
