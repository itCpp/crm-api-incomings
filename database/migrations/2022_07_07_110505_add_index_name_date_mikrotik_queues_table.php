<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexNameDateMikrotikQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mikrotik_queues', function (Blueprint $table) {
            $table->index(['name', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mikrotik_queues', function (Blueprint $table) {
            $table->dropIndex(['name', 'date']);
        });
    }
}
