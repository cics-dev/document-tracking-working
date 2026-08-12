<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_flow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->enum('stage_type', ['routing', 'signatory', 'action']);
            $table->string('label');
            $table->unsignedInteger('sequence');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_selectable')->default(false);
            $table->enum('condition', ['always', 'with_budget', 'without_budget'])->default('always');
            $table->timestamps();
            $table->index(['document_type_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_flow_stages');
    }
};
