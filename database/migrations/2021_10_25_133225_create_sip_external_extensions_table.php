<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSipExternalExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sip_external_extensions', function (Blueprint $table) {
            $table->id();
            $table->string('extension', 255)->nullable();
            $table->string('belongs', 255)->nullable()->comment("Принадлежность к оператору связи");
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
        Schema::dropIfExists('sip_external_extensions');
    }
}
