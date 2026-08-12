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
            $table->string('workflow_key', 50)->nullable()->unique()->after('abbreviation');
        });

        foreach ([
            'university_president' => 'Office of the University President',
            'vpaf' => 'Office of the Vice President for Administration and Finance',
            'records' => 'Records Section',
            'sao_admin' => 'Office of the Supervising Administrative Officer for Admin',
            'budget' => 'Budget Office',
            'motor_pool' => 'Motorpool Office',
            'legal' => 'Legal Office',
            'igp' => 'Income Generating Program Office',
            'cao' => 'Office of the Chief Administrative Officer',
            'sao_finance' => 'Office of the Supervising Administrative Officer for Finance',
        ] as $key => $name) {
            DB::table('offices')->where('name', $name)->update(['workflow_key' => $key]);
        }
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropUnique(['workflow_key']);
            $table->dropColumn('workflow_key');
        });
    }
};
