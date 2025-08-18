<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToMenuCategoriesTable extends Migration
{
    public function up()
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}