<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_nha_hang', function (Blueprint $table) {
            $table->id();
            $table->string('img');                    // đường dẫn ảnh menu
            $table->unsignedInteger('sort_order')     // ← CỘT MỚI: thứ tự sắp xếp
            ->default(0);
            $table->timestamps();                     // created_at + updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_nha_hang');
    }
};