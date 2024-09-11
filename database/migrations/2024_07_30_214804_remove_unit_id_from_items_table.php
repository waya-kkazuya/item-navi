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
            $table->dropForeign(['unit_id']); // 外部キー制約を削除
            $table->dropColumn('unit_id'); // unit_idカラムを削除
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('unit_id')->constrained('units'); // unit_idカラムを再作成し、外部キー制約を設定
        });
    }
};
