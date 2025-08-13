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
        Schema::create('danhmuc_bx', function (Blueprint $table) {
            $table->id();
            $table->string('ten_danh_muc');
            $table->timestamps();

            // Index cho performance
            $table->index(['ten_danh_muc']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danhmuc_bx');
    }
};