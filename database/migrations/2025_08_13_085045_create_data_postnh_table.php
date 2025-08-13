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
        Schema::create('data_postnh', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('id_danhmuc_data')->nullable();
            $table->timestamps();

            // Foreign key constraint - trỏ đến bảng danhmuc_n_h_s
            $table->foreign('id_danhmuc_data')->references('id')->on('danhmuc_n_h_s')->onDelete('set null');

            // Index cho performance
            $table->index(['type']);
            $table->index(['id_danhmuc_data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_postnh');
    }
};