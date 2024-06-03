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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained();
            $table->date('scheduled_date');
            $table->date('inspection_date');
            $table->text('details');
            $table->string('inspection_person');
            $table->boolean('status'); // 実施したらtrue
            $table->date('next_scheduled_date'); // 自動計算機能（間隔は法律で決まっている？）、後から入力も出来る
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
