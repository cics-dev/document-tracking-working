<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->string('approver_display_mode')->default('labeled')->after('show_approval_action');
        });

        DB::table('document_types')
            ->where('show_approval_action', true)
            ->update(['approver_display_mode' => 'action_box']);
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('approver_display_mode');
        });
    }
};
