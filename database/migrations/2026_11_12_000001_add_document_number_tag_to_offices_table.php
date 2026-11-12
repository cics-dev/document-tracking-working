<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->string('document_number_tag', 50)->nullable();
        });

        // Include archived offices and preserve their current numbering format.
        DB::table('offices')->update([
            'document_number_tag' => DB::raw("NULLIF(office_type, '')"),
        ]);
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('document_number_tag');
        });
    }
};
