<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('document_types')
            ->where('name', 'Office Memorandum')
            ->update(['name' => 'Intra-Office Memorandum', 'abbreviation' => 'Intra']);
    }

    public function down(): void
    {
        DB::table('document_types')
            ->where('name', 'Intra-Office Memorandum')
            ->where('abbreviation', 'Intra')
            ->update(['name' => 'Office Memorandum', 'abbreviation' => '']);
    }
};
