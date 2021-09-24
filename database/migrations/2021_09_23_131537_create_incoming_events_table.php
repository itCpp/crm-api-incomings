<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomingEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoming_events', function (Blueprint $table) {
            $table->id();
            $table->string('api_type', 100)->nullable()->comment("Тип события");
            $table->string('ip', 100)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('request_data')->comment("Полученные данные");
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
        Schema::dropIfExists('incoming_events');
    }
}
