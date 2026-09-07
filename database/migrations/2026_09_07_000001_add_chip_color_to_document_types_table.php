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
            $table->string('chip_color', 20)->default('blue')->after('abbreviation');
        });

        $colors = ['blue', 'emerald', 'violet', 'amber', 'rose', 'cyan', 'orange', 'indigo', 'teal', 'pink', 'lime', 'purple'];

        DB::table('document_types')->orderBy('id')->get(['id'])->each(function ($type, $index) use ($colors) {
            DB::table('document_types')->where('id', $type->id)->update([
                'chip_color' => $colors[$index % count($colors)],
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('chip_color');
        });
    }
};
