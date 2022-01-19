<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateTelegramIncomingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_incomings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('message_id')->nullable();
            $table->bigInteger('chat_id')->nullable();
            $table->bigInteger('from_id')->nullable();
            $table->json('request_data')->default(new Expression('(JSON_ARRAY())'));
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
        Schema::dropIfExists('telegram_incomings');
    }
}
