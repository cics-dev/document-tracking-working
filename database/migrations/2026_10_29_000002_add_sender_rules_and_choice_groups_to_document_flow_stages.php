<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_flow_stages', function (Blueprint $table) {
            $table->foreignId('sender_role_id')->nullable()->constrained('roles')->restrictOnDelete();
            $table->string('sender_role_operator')->default('equals');
            $table->string('selection_group', 80)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('document_flow_stages', function (Blueprint $table) {
            $table->dropForeign(['sender_role_id']);
            $table->dropColumn(['sender_role_id', 'sender_role_operator', 'selection_group']);
        });
    }
};
