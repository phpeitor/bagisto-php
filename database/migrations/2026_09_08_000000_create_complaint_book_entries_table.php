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
        Schema::create('complaint_book_entries', function (Blueprint $table) {
            $table->id();
            $table->string('correlative')->unique();
            $table->enum('type', ['reclamo', 'queja']);

            // Consumer.
            $table->enum('document_type', ['dni', 'ce', 'pasaporte']);
            $table->string('document_number');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('email');

            // Good or service.
            $table->enum('good_type', ['producto', 'servicio']);
            $table->string('good_description');
            $table->decimal('claimed_amount', 10, 2)->nullable();

            // Claim.
            $table->text('detail');
            $table->text('request');
            $table->string('status')->default('pendiente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_book_entries');
    }
};
