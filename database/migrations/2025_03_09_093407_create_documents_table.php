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
            $table->foreignId('from_id')->constrained('offices')->onDelete('cascade');
            $table->foreignId('to_id')->nullable()->constrained('offices')->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade');
            $table->string('thru')->nullable();
            $table->string('subject');
            $table->text('content');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('draft');
            $table->date('date_sent')->nullable();
            $table->string('file_url')->nullable();
            $table->string('document_level')->default('Inter'); //Inter, Intra, External
            $table->string('to_text')->nullable(); //Inter, Intra, External
            $table->boolean('is_revision')->nullable();
            $table->foreignId('original_document_id')->nullable()->constrained('documents')->onDelete('cascade');
            $table->timestamps();
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
