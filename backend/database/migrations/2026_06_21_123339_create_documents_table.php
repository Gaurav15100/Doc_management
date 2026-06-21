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
        Schema::create('documents', function (Blueprint $table) {

            $table->id();

            $table->string('doc_number');

            $table->string('file_mode');

            $table->unsignedBigInteger('document_type_id');

            $table->unsignedBigInteger('outlet_id');

            $table->unsignedBigInteger('party_id')
                ->nullable();

            $table->unsignedBigInteger('uploaded_by');

            $table->enum('status', [
                'uploaded',
                'query_raised',
                'processed'
            ])->default('uploaded');

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
