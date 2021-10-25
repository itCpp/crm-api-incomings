<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSipInternalExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sip_internal_extensions', function (Blueprint $table) {
            $table->id();
            $table->string('extension', 255)->nullable();
            $table->string('internal_addr', 100)->nullable()->comment("Внутренний адрес устройства");
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
        Schema::dropIfExists('sip_internal_extensions');
    }
}
