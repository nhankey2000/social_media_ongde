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
        Schema::create('data_imagesnh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->string('type')->nullable();
            $table->string('url');
            $table->unsignedBigInteger('id_danhmuc_data')->nullable();
            $table->timestamps(); // Thêm dòng này
            // Không có timestamps vì Model đã set $timestamps = false

            // Foreign key constraints
            $table->foreign('post_id')->references('id')->on('data_postnh')->onDelete('cascade');
            $table->foreign('id_danhmuc_data')->references('id')->on('danhmuc_n_h_s')->onDelete('set null');

            // Index cho performance
            $table->index(['post_id']);
            $table->index(['type']);
            $table->index(['id_danhmuc_data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_imagesnh');
    }
};