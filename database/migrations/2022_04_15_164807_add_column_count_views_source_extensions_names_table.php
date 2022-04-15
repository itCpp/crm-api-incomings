<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCountViewsSourceExtensionsNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('source_extensions_names', function (Blueprint $table) {
            $table->integer('views')->default(0)->comment("Количество запросов")->after("abbr_name");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('source_extensions_names', function (Blueprint $table) {
            $table->dropColumn('views');
        });
    }
}
