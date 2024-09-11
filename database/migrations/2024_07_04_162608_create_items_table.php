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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('image1')->nullable();
            $table->string('image2')->nullable();
            $table->string('image3')->nullable();
            $table->unsignedInteger('stock');
            $table->unsignedInteger('minimum_stock')->nullable();
            $table->foreignId('usage_status_id')->constrained('usage_statuses');
            $table->string('end_user')->nullable(); 
            $table->unsignedBigInteger('location_of_use_id');
            $table->foreign('location_of_use_id')->references('id')->on('locations');
            $table->unsignedBigInteger('storage_location_id');
            $table->foreign('storage_location_id')->references('id')->on('locations');
            $table->foreignId('acquisition_method_id')->constrained('acquisition_methods');
            $table->string('where_to_buy')->nullable();
            $table->unsignedInteger('price');
            $table->date('date_of_acquisition')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('product_number')->nullable();
            $table->text('remarks')->nullable();
            $table->string('qrcode')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
