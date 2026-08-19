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
            $table->string('print_layout')->default('memorandum')->after('content_template');
        });

        DB::table('document_types')->where('name', 'Indorsement Letter')->update(['print_layout' => 'indorsement']);
        DB::table('document_types')->where('name', 'External Communication Response Letter')->update(['print_layout' => 'letter']);
        DB::table('document_types')->where('name', 'Special Order')->update(['print_layout' => 'special_order']);
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('print_layout');
        });
    }
};
