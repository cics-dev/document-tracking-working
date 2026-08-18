<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_steps', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        DB::table('document_steps')->orderBy('id')->each(function ($step): void {
            $office = $step->office_id ? DB::table('offices')->where('id', $step->office_id)->first() : null;
            $actor = $step->user_id ? DB::table('users')->where('id', $step->user_id)->first() : null;
            $head = $office?->head_id ? DB::table('users')->where('id', $office->head_id)->first() : null;

            $assignedUserId = $step->user_id;
            if ($step->processed_at && $head && ($step->signatory_name === null || $step->signatory_name === $head->name)) {
                $assignedUserId = $head->id;
            }

            $signedFor = $step->processed_at && $assignedUserId && $step->user_id !== $assignedUserId;
            DB::table('document_steps')->where('id', $step->id)->update([
                'assigned_user_id' => $assignedUserId,
                'signatory_name' => $step->signatory_name ?? ($head?->name ?? $actor?->name),
                'signatory_position' => $step->signatory_position ?? ($head?->position ?? $actor?->position),
                'signature_path' => $step->signature_path ?? ($step->processed_at ? $actor?->signature : null),
                'signed_for' => $signedFor,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('document_steps', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
        });
    }
};
