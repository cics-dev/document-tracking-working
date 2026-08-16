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
            $table->string('recipient_mode')->default('office')->after('abbreviation');
            $table->string('recipient_label')->default('To')->after('recipient_mode');
            $table->string('recipient_office_key')->nullable()->after('recipient_label');
            $table->string('document_level')->default('Inter')->after('recipient_office_key');
            $table->string('number_prefix')->nullable()->after('document_level');
            $table->boolean('show_thru')->default(true)->after('number_prefix');
            $table->boolean('show_carbon_copy')->default(true)->after('show_thru');
            $table->boolean('requires_signatories')->default(false)->after('show_carbon_copy');
            $table->boolean('is_publicly_creatable')->default(false)->after('requires_signatories');
            $table->text('content_template')->nullable()->after('is_publicly_creatable');
        });

        DB::table('document_types')->where('abbreviation', 'RLM')->update([
            'recipient_label' => 'For', 'recipient_office_key' => 'university_president',
            'requires_signatories' => true, 'is_publicly_creatable' => true,
        ]);
        DB::table('document_types')->whereIn('abbreviation', ['ECLR', 'Intra'])->update(['recipient_mode' => 'text']);
        DB::table('document_types')->where('abbreviation', 'Intra')->update([
            'document_level' => 'Intra', 'number_prefix' => 'CM-{office}', 'show_thru' => false,
        ]);
        DB::table('document_types')->where('abbreviation', 'ECLR')->update([
            'number_prefix' => 'ZPPSU-{office_with_type}-{type}',
            'content_template' => '<strong>{TO}</strong><p>[insert position here]</p><p>[insert office here]</p><p>[insert office address here]</p><br><br><p>Subject: <b>{SUBJECT}</b></p><br><br>[Insert your salutation]<br><br>[Start your message here]',
        ]);
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn(['recipient_mode', 'recipient_label', 'recipient_office_key', 'document_level', 'number_prefix', 'show_thru', 'show_carbon_copy', 'requires_signatories', 'is_publicly_creatable', 'content_template']);
        });
    }
};
