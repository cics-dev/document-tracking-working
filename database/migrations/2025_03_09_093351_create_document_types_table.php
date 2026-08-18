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
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('abbreviation')->unique();
            $table->string('recipient_mode')->default('office');
            $table->string('recipient_label')->default('To');
            $table->foreignId('recipient_office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->string('document_level')->default('Inter');
            $table->string('number_prefix')->nullable();
            $table->boolean('show_thru')->default(true);
            $table->boolean('show_carbon_copy')->default(true);
            $table->boolean('allow_attachments')->default(true);
            $table->boolean('requires_signatories')->default(false);
            $table->boolean('is_publicly_creatable')->default(false);
            $table->text('content_template')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
