<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexes();

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['from_id']);
            $table->dropForeign(['to_id']);
            $table->dropForeign(['document_type_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['original_document_id']);
            $table->foreign('from_id')->references('id')->on('offices')->restrictOnDelete();
            $table->foreign('to_id')->references('id')->on('offices')->nullOnDelete();
            $table->foreign('document_type_id')->references('id')->on('document_types')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('original_document_id')->references('id')->on('documents')->nullOnDelete();
        });

        Schema::table('document_steps', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['office_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('office_id')->references('id')->on('offices')->nullOnDelete();
        });

        Schema::table('external_documents', function (Blueprint $table) {
            $table->dropForeign(['to_id']);
            $table->dropForeign(['document_id']);
            $table->foreign('to_id')->references('id')->on('offices')->restrictOnDelete();
            $table->foreign('document_id')->references('id')->on('documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('external_documents', function (Blueprint $table) {
            $table->dropForeign(['to_id']);
            $table->dropForeign(['document_id']);
            $table->foreign('to_id')->references('id')->on('offices')->cascadeOnDelete();
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();
        });

        Schema::table('document_steps', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['office_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('office_id')->references('id')->on('offices')->cascadeOnDelete();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['from_id']);
            $table->dropForeign(['to_id']);
            $table->dropForeign(['document_type_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['original_document_id']);
            $table->foreign('from_id')->references('id')->on('offices')->cascadeOnDelete();
            $table->foreign('to_id')->references('id')->on('offices')->cascadeOnDelete();
            $table->foreign('document_type_id')->references('id')->on('document_types')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('original_document_id')->references('id')->on('documents')->cascadeOnDelete();
        });

        Schema::table('external_documents', function (Blueprint $table) {
            $table->dropIndex(['to_id', 'received_date']);
            $table->dropIndex(['document_number', 'created_at']);
        });

        Schema::table('document_access_logs', function (Blueprint $table) {
            $table->dropIndex('dal_user_action_document_index');
        });

        Schema::table('document_logs', function (Blueprint $table) {
            $table->dropIndex(['document_id', 'user_id', 'action', 'created_at']);
        });

        Schema::table('document_steps', function (Blueprint $table) {
            $table->dropIndex(['document_id', 'status', 'sequence', 'id']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['from_id', 'document_type_id', 'status']);
            $table->dropIndex(['to_id', 'status']);
            $table->dropIndex(['created_by', 'status']);
            $table->dropIndex(['document_level', 'status']);
        });

        Schema::table('document_carbon_copies', function (Blueprint $table) {
            $table->dropUnique(['document_id', 'user_id']);
            $table->dropIndex(['user_id', 'document_id']);
        });

        Schema::table('role_document_types', function (Blueprint $table) {
            $table->dropUnique(['role_id', 'document_type_id']);
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }

    private function addIndexes(): void
    {
        if (! Schema::hasIndex('user_profiles', 'user_profiles_user_id_unique')) {
            Schema::table('user_profiles', fn (Blueprint $table) => $table->unique('user_id'));
        }

        if (! Schema::hasIndex('role_document_types', 'role_document_types_role_id_document_type_id_unique')) {
            Schema::table('role_document_types', fn (Blueprint $table) => $table->unique(['role_id', 'document_type_id']));
        }

        if (! Schema::hasIndex('document_carbon_copies', 'document_carbon_copies_document_id_user_id_unique')) {
            Schema::table('document_carbon_copies', fn (Blueprint $table) => $table->unique(['document_id', 'user_id']));
        }

        if (! Schema::hasIndex('document_carbon_copies', 'document_carbon_copies_user_id_document_id_index')) {
            Schema::table('document_carbon_copies', fn (Blueprint $table) => $table->index(['user_id', 'document_id']));
        }

        if (! Schema::hasIndex('documents', 'documents_from_id_document_type_id_status_index')) {
            Schema::table('documents', fn (Blueprint $table) => $table->index(['from_id', 'document_type_id', 'status']));
        }

        if (! Schema::hasIndex('documents', 'documents_to_id_status_index')) {
            Schema::table('documents', fn (Blueprint $table) => $table->index(['to_id', 'status']));
        }

        if (! Schema::hasIndex('documents', 'documents_created_by_status_index')) {
            Schema::table('documents', fn (Blueprint $table) => $table->index(['created_by', 'status']));
        }

        if (! Schema::hasIndex('documents', 'documents_document_level_status_index')) {
            Schema::table('documents', fn (Blueprint $table) => $table->index(['document_level', 'status']));
        }

        if (! Schema::hasIndex('document_steps', 'document_steps_document_id_status_sequence_id_index')) {
            Schema::table('document_steps', fn (Blueprint $table) => $table->index(['document_id', 'status', 'sequence', 'id']));
        }

        if (! Schema::hasIndex('document_steps', 'document_steps_user_id_status_index')) {
            Schema::table('document_steps', fn (Blueprint $table) => $table->index(['user_id', 'status']));
        }

        if (! Schema::hasIndex('document_logs', 'document_logs_document_id_user_id_action_created_at_index')) {
            Schema::table('document_logs', fn (Blueprint $table) => $table->index(['document_id', 'user_id', 'action', 'created_at']));
        }

        if (! Schema::hasIndex('document_access_logs', 'dal_user_action_document_index')) {
            Schema::table('document_access_logs', fn (Blueprint $table) => $table->index(['user_id', 'action', 'documentable_type', 'documentable_id'], 'dal_user_action_document_index'));
        }

        if (! Schema::hasIndex('external_documents', 'external_documents_to_id_received_date_index')) {
            Schema::table('external_documents', fn (Blueprint $table) => $table->index(['to_id', 'received_date']));
        }

        if (! Schema::hasIndex('external_documents', 'external_documents_document_number_created_at_index')) {
            Schema::table('external_documents', fn (Blueprint $table) => $table->index(['document_number', 'created_at']));
        }
    }
};
