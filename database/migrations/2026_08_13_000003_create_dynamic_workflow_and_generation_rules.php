<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_generation_rules', function (Blueprint $table) {
            $table->id();
            $table->enum('source_context', ['internal', 'external']);
            $table->foreignId('source_document_type_id')->nullable()->constrained('document_types')->cascadeOnDelete();
            $table->foreignId('target_document_type_id')->constrained('document_types')->cascadeOnDelete();
            $table->string('button_label');
            $table->string('required_status')->nullable();
            $table->boolean('requires_assigned_office')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['source_context', 'source_document_type_id', 'target_document_type_id'], 'generation_rule_unique');
        });
        Schema::create('document_generation_rule_role', function (Blueprint $table) {
            $table->unsignedBigInteger('document_generation_rule_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('document_generation_rule_id', 'generation_rule_role_rule_fk')->references('id')->on('document_generation_rules')->cascadeOnDelete();
            $table->foreign('role_id', 'generation_rule_role_role_fk')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['document_generation_rule_id', 'role_id'], 'generation_rule_role_primary');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('document_generation_rule_role');
        Schema::dropIfExists('document_generation_rules');
    }
};
