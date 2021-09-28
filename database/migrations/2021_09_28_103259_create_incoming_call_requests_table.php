<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomingCallRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoming_call_requests', function (Blueprint $table) {
            $table->id();
            $table->string('api_type', 100)->nullable()->comment("Источник события");
            $table->integer('incoming_event_id')->nullable()->comment("Идентификатор события");
            $table->integer('request_count')->default(0)->comment("Количество попыток отправки запроса в CRM");
            $table->integer('response_code')->nullable()->comment("Код ответа CRM");
            $table->json('response_data')->nullable()->comment("Ответ CRM");
            $table->timestamp('sent_at')->nullable()->comment("Время отправки запроса в CRM");
            $table->timestamp('processed_at')->nullable()->comment("Время завершения обработки запроса в CRM");
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
        Schema::dropIfExists('incoming_call_requests');
    }
}
