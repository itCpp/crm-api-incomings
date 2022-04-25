<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHashColumnIncomingCallRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoming_call_requests', function (Blueprint $table) {
            $table->string('event_hash')->nullable()->comment("Хэш запроса")->after('incoming_event_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incoming_call_requests', function (Blueprint $table) {
            $table->dropColumn('event_hash');
        });
    }
}
