<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMikrotikQueuesLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mikrotik_queues_limits', function (Blueprint $table) {
            $table->id();
            $table->string("name")->index();
            $table->string("limit_up")->nullable();
            $table->string("limit_down")->nullable();
            $table->bigInteger("limit")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mikrotik_queues_limits');
    }
}
