<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', fn (Blueprint $table) =>
            $table->boolean('allow_attachments')->default(true)->after('show_carbon_copy')
        );
    }

    public function down(): void
    {
        Schema::table('document_types', fn (Blueprint $table) => $table->dropColumn('allow_attachments'));
    }
};
