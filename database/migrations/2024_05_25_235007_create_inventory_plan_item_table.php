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
        Schema::create('inventory_plan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_plan_id')->constrained()->onUpdate('cascade');
            $table->foreignId('item_id')->constrained()->onUpdate('cascade');
            $table->dateTime('inventory_date');
            // $table->unsignedInteger('stocks');
            $table->string('inventory_person');
            $table->boolean('insuffcient_data_status');
            $table->text('insuffcient_data_details')->nullable();
            $table->boolean('unknown_assets_status');
            $table->text('unknown_assets_details')->nullable();
            $table->boolean('inventory_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_plan_item');
    }
};
