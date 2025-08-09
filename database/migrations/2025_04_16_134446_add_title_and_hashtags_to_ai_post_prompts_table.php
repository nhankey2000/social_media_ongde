<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_post_prompts', function (Blueprint $table) {
            $table->string('title')->nullable()->after('generated_content');
            $table->json('hashtags')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('ai_post_prompts', function (Blueprint $table) {
            $table->dropColumn(['title', 'hashtags']);
        });
    }
};