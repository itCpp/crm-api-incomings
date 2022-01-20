<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateTelegramOutgoingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_outgoings', function (Blueprint $table) {
            $table->id();
            $table->string('method', 255)->nullable();
            $table->text('bot_token')->nullable();
            $table->json('request_data')->default(new Expression('(JSON_ARRAY())'));
            $table->integer('response_code')->default(0);
            $table->json('response_data')->default(new Expression('(JSON_ARRAY())'));
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telegram_outgoings');
    }
}
