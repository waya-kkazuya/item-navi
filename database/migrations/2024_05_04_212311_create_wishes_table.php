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
        Schema::create('wishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained('categories');
            // $table->string('category');
            $table->string('vendor');
            $table->string('location_of_use');
            $table->string('storage_location');
            $table->double('price')->nullable();
            $table->string('reference_site_url', 2048)->nullable();
            $table->string('applicant');
            $table->text('comment_from_applicant')->nullable();
            $table->string('decision_status')->nullable();
            $table->text('comment_from_administrator')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishes');
    }
};
