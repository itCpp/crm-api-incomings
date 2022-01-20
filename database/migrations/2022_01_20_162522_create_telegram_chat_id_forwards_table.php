<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelegramChatIdForwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_chat_id_forwards', function (Blueprint $table) {
            $table->bigInteger('from_chat_id')->comment('Из какой группы копировать сообщения')->index();
            $table->bigInteger('to_chat_id')->comment('В какую группу копировать сообщения');
            $table->unique(['from_chat_id', 'to_chat_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telegram_chat_id_forwards');
    }
}
