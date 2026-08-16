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
            $table->foreignId('recipient_office_id')->nullable()->after('recipient_label')
                ->constrained('offices')->nullOnDelete();
        });

        DB::table('document_types')->whereNotNull('recipient_office_key')->orderBy('id')->each(function ($type) {
            $officeId = DB::table('offices')->where('workflow_key', $type->recipient_office_key)->value('id');
            if ($officeId) DB::table('document_types')->where('id', $type->id)->update(['recipient_office_id' => $officeId]);
        });

        Schema::table('document_types', fn (Blueprint $table) => $table->dropColumn('recipient_office_key'));
    }

    public function down(): void
    {
        Schema::table('document_types', fn (Blueprint $table) => $table->string('recipient_office_key')->nullable()->after('recipient_label'));
        DB::table('document_types')->whereNotNull('recipient_office_id')->orderBy('id')->each(function ($type) {
            $key = DB::table('offices')->where('id', $type->recipient_office_id)->value('workflow_key');
            if ($key) DB::table('document_types')->where('id', $type->id)->update(['recipient_office_key' => $key]);
        });
        Schema::table('document_types', fn (Blueprint $table) => $table->dropConstrainedForeignId('recipient_office_id'));
    }
};
