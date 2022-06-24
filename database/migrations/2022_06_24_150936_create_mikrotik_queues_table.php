<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMikrotikQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mikrotik_queues', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->index();
            $table->string('month', 7)->nullable()->index();
            $table->string("date", 10)->nullable()->index();
            $table->bigInteger('uploads')->default(0);
            $table->bigInteger('downloads')->default(0);
            $table->timestamps();

            $table->index(['name', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mikrotik_queues');
    }
}
