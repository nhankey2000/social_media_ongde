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
        // Tạo bảng danh mục menu
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên danh mục
            $table->timestamps();
        });

        // Tạo bảng images_menu
        Schema::create('images_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->constrained('menu_categories')->onDelete('cascade'); // Liên kết với bảng menu_categories
            $table->string('image_path'); // Đường dẫn ảnh
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images_menu');
        Schema::dropIfExists('menu_categories');
    }
};