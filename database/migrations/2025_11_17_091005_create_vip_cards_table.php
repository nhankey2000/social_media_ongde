<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_cards', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // GOLD, SILVER, PLATINUM
            $table->text('content'); // Nội dung ưu đãi
            $table->date('expiry_date'); // Thời hạn sử dụng
            $table->date('created_date')->useCurrent(); // Ngày tạo
            $table->date('updated_date')->nullable(); // Ngày cập nhật
            $table->boolean('status')->default(true); // Trạng thái bật/tắt
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_cards');
    }
};