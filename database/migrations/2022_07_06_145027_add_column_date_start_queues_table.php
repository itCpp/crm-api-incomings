<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDateStartQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mikrotik_queues_limits', function (Blueprint $table) {
            $table->string("start", 10)->nullable()->comment("Дата начала использования интернета")->after('limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mikrotik_queues_limits', function (Blueprint $table) {
            $table->dropColumn('start');
        });
    }
}
