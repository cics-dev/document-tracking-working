<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('offices', fn (Blueprint $table) => $table->softDeletes());

        Schema::table('document_steps', function (Blueprint $table) {
            $table->string('signatory_name')->nullable()->after('step_label');
            $table->string('signatory_position')->nullable()->after('signatory_name');
            $table->string('signature_path')->nullable()->after('signatory_position');
            $table->boolean('signed_for')->default(false)->after('signature_path');
        });

        DB::table('document_steps')->where('step_type', 'signatory')->orderBy('id')->each(function ($step) {
            $office = $step->office_id ? DB::table('offices')->where('id', $step->office_id)->first() : null;
            $head = $office?->head_id ? DB::table('users')->where('id', $office->head_id)->first() : null;
            $actor = DB::table('users')->where('id', $step->user_id)->first();
            DB::table('document_steps')->where('id', $step->id)->update([
                'signatory_name' => $head?->name ?? $actor?->name,
                'signatory_position' => $head?->position ?? $actor?->position,
                'signature_path' => $step->processed_at ? $actor?->signature : null,
                'signed_for' => (bool) ($step->processed_at && $office?->head_id && $office->head_id !== $step->user_id),
            ]);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::table('document_steps', fn (Blueprint $table) => $table->dropColumn(['signatory_name', 'signatory_position', 'signature_path', 'signed_for']));
        Schema::table('offices', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('users', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
