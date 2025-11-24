<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();

            $table->string('reporter_name');
            $table->bigInteger('reporter_telegram_id')->nullable();
            $table->string('reporter_username')->nullable();

            $table->text('content');
            $table->text('ai_response')->nullable();

            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'overdue'
            ])->default('pending');

            $table->enum('priority', [
                'low',
                'medium',
                'high'
            ])->default('low');

            $table->datetime('deadline')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->string('completed_by')->nullable();

            $table->json('metadata')->nullable();
            $table->integer('processing_time')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('location_id');
            $table->index('status');
            $table->index('priority');
            $table->index(['status', 'priority']);
            $table->index('deadline');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};