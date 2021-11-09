<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateCallsSectorQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calls_sector_queues', function (Blueprint $table) {
            $table->id();
            $table->string('sip_server', 100)->comment('Адрес или идентификатор sip-сервера')->nullable();
            $table->string('last_sector_id', 10)->comment('Идентификатор сектора для переадресации звонка')->nullable();
            $table->integer('next_to_count')->default(0)->comment('Счетчик выданных звонков перед сменой сектора');
            $table->date('date')->nullable()->comment('Дата работы скрипта');
            $table->json('data_counters')->default(new Expression('(JSON_ARRAY())'))->comment('Подробный счетчик распределения звонков');
            $table->integer('counter')->default(0);
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
        Schema::dropIfExists('calls_sector_queues');
    }
}
