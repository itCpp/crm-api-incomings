<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMikrotikAdditionalTrafficTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mikrotik_additional_traffic', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('queue_id')->nullable();
            $table->bigInteger('traffic')->default(0)->comment("Дополнительный трафик");
            $table->date('date')->comment("Дата добавления");
            $table->timestamps();
            $table->softDeletes();
            $table->index(['queue_id', 'date', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mikrotik_additional_traffic');
    }
}
