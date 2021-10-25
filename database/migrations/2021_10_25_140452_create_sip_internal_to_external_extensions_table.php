<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSipInternalToExternalExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sip_internal_to_external_extensions', function (Blueprint $table) {
            $table->bigInteger('internal_id');
            $table->bigInteger('external_id');
            $table->unique(['internal_id', 'external_id'], 'internal_id_external_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sip_internal_to_external_extensions');
    }
}
