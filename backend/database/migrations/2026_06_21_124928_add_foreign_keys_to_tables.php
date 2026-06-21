<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('outlet_id')
                ->references('id')
                ->on('outlets')
                ->nullOnDelete();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('document_type_id')
                ->references('id')
                ->on('document_types');

            $table->foreign('outlet_id')
                ->references('id')
                ->on('outlets');

            $table->foreign('party_id')
                ->references('id')
                ->on('parties')
                ->nullOnDelete();

            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users');
        });

        Schema::table('document_files', function (Blueprint $table) {
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete();
        });

        Schema::table('document_links', function (Blueprint $table) {
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete();

            $table->foreign('linked_document_id')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete();
        });

        Schema::table('queries', function (Blueprint $table) {
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete();

            $table->foreign('raised_by')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
