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
        Schema::table('edithistories', function (Blueprint $table) {
            $table->dropForeign(['edit_reason_id']);
            $table->dropColumn('edit_reason_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('edithistories', function (Blueprint $table) {
            $table->foreignId('edit_reason_id')->after('edit_user')->constrained('edit_reasons');
        });
    }
};
