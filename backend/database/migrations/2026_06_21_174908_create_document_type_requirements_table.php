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
        Schema::create('document_type_requirements', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('document_type_id');

            $table->unsignedBigInteger('required_document_type_id');

            $table->timestamps();

            $table->unique(
                ['document_type_id', 'required_document_type_id'],
                'doc_type_req_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_type_requirements');
    }
};
