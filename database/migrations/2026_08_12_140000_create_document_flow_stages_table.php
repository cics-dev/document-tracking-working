<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->enum('input_type', ['boolean', 'select', 'text', 'number'])->default('boolean');
            $table->json('options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('document_flow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->enum('stage_type', ['routing', 'signatory', 'action']);
            $table->string('label');
            $table->text('description')->nullable();
            $table->unsignedInteger('sequence');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_selectable')->default(false);
            $table->foreignId('workflow_condition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('condition_operator')->default('equals');
            $table->string('condition_value')->nullable();
            $table->timestamps();
            $table->index(['document_type_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_flow_stages');
        Schema::dropIfExists('workflow_conditions');
    }
};
