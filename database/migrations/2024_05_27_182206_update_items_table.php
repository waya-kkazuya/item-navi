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
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('location_of_use');
            $table->dropColumn('storage_location');
            $table->unsignedBigInteger('location_of_use_id')->after('end_user');
            $table->unsignedBigInteger('storage_location_id')->after('location_of_use_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('location_of_use_id');
            $table->dropColumn('storage_location_id');
            $table->string('location_of_use')->after('end_user');
            $table->string('storage_location')->after('location_of_use');
        });
    }
};
