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
            $table->boolean('show_approval_action')->default(false)->after('sender_signature_policy');
        });

        DB::table('document_types')
            ->whereIn('name', ['Request Letter Memorandum', 'Indorsement Letter'])
            ->update(['show_approval_action' => true]);
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('show_approval_action');
        });
    }
};
