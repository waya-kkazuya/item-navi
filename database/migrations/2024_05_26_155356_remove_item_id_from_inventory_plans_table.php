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
        Schema::table('inventory_plans', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign('inventory_plans_item_id_foreign');
            // 列を削除
            $table->dropColumn('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            // 列を追加
            $table->integer('item_id')->after('name');
            // 外部キー制約を追加
            $table->foreign('item_id')->references('id')->on('items');
            
            // $table->foreignId('item_id')->constrained()->onUpdate('cascade')->after('name');
        });
    }
};
