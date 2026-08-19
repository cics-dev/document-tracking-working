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
            $table->string('sender_signature_policy')->default('approved')->after('print_layout');
        });

        DB::table('document_types')
            ->where('name', 'Request Letter Memorandum')
            ->update(['sender_signature_policy' => 'always']);
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('sender_signature_policy');
        });
    }
};
