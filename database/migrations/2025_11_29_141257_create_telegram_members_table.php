<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->bigInteger('telegram_id')->unsigned();
            $table->string('username')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('full_name');
            $table->string('role')->nullable(); // Vai trò: IT, Bảo trì, Kế toán, etc.
            $table->json('keywords')->nullable(); // Keywords để nhận diện: ["IT", "máy tính", "phần mềm"]
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['location_id', 'telegram_id']);
            $table->index('username');
            $table->index('role');
        });

        // Bảng lưu lịch sử giao việc
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('telegram_member_id')->constrained()->onDelete('cascade');
            $table->string('task_description');
            $table->timestamp('assigned_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('assigned'); // assigned, acknowledged, completed, overdue
            $table->timestamps();

            $table->index(['report_id', 'status']);
            $table->index('assigned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
        Schema::dropIfExists('telegram_members');
    }
};