<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const COLORS = [
        'blue' => '#dbeafe',
        'emerald' => '#d1fae5',
        'violet' => '#ede9fe',
        'amber' => '#fef3c7',
        'rose' => '#ffe4e6',
        'cyan' => '#cffafe',
        'orange' => '#ffedd5',
        'indigo' => '#e0e7ff',
        'teal' => '#ccfbf1',
        'pink' => '#fce7f3',
        'lime' => '#ecfccb',
        'purple' => '#f3e8ff',
    ];

    public function up(): void
    {
        foreach (self::COLORS as $name => $hex) {
            DB::table('document_types')->where('chip_color', $name)->update(['chip_color' => $hex]);
        }
    }

    public function down(): void
    {
        foreach (self::COLORS as $name => $hex) {
            DB::table('document_types')->where('chip_color', $hex)->update(['chip_color' => $name]);
        }
    }
};
