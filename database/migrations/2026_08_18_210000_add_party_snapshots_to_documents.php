<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('from_name')->nullable()->after('from_id');
            $table->string('from_position')->nullable()->after('from_name');
            $table->string('to_name')->nullable()->after('to_id');
            $table->string('to_position')->nullable()->after('to_name');
        });

        DB::table('documents')->orderBy('id')->each(function ($document): void {
            $fromOffice = DB::table('offices')->where('id', $document->from_id)->first();
            $fromUser = $fromOffice?->head_id ? DB::table('users')->where('id', $fromOffice->head_id)->first() : null;
            $toOffice = $document->to_id ? DB::table('offices')->where('id', $document->to_id)->first() : null;
            $toUser = $toOffice?->head_id ? DB::table('users')->where('id', $toOffice->head_id)->first() : null;

            DB::table('documents')->where('id', $document->id)->update([
                'from_name' => $fromUser?->name,
                'from_position' => $fromUser?->position,
                'to_name' => $toUser?->name,
                'to_position' => $toUser?->position,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['from_name', 'from_position', 'to_name', 'to_position']);
        });
    }
};
