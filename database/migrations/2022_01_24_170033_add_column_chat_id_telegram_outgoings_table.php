<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnChatIdTelegramOutgoingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telegram_outgoings', function (Blueprint $table) {
            if (!Schema::hasColumn('telegram_outgoings', 'chat_id')) {
                $table->bigInteger('chat_id')->nullable()->after('bot_token')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
