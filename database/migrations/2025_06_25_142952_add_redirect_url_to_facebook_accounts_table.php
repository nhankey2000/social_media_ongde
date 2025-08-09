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
        Schema::table('facebook_accounts', function (Blueprint $table) {
            $table->string('redirect_url', 2048)->nullable()->after('access_token');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_accounts', function (Blueprint $table) {
            $table->dropColumn('redirect_url');
        });
    }

};
