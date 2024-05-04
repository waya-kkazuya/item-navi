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
            // $table->string('category');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('image_path1')->nullable();
            $table->string('image_path2')->nullable();
            $table->string('image_path3')->nullable();
            $table->unsignedInteger('stocks');
            // $table->foreignId('stock_id')->constrained();
            $table->string('usage_status');
            $table->string('end_user')->nullable();
            // $table->foreignId('end_user_id')->constrained();
            $table->string('location_of_use');
            // $table->foreignId('location_of_use_id')->constrained();
            $table->string('storage_location');
            // $table->foreignId('storage_location_id')->constrained();
            $table->string('acquisition_category')->nullable();
            $table->double('price')->nullable();
            $table->date('date_of_acquisition')->nullable();
            $table->date('inspection_schedule')->nullable();
            // $table->foreignId('inspection_schedule_id')->constrained();
            $table->date('disposal_schedule')->nullable();
            // $table->foreignId('disposal_schedule_id')->constrained();
            $table->string('manufacturer')->nullable();
            $table->unsignedInteger('product_number')->nullable();
            $table->string('vendor');
            $table->string('vendor_website_url', 2048)->nullable();
            $table->text('remarks')->nullable();
            $table->string('qrcode_path')->nullable();
            $table->timestamps();
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
