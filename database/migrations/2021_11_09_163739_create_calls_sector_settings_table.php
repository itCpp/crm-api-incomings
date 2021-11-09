<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallsSectorSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calls_sector_settings', function (Blueprint $table) {
            $table->bigInteger('id')->unique();
            $table->string('name', 50)->comment('Наименование сектора')->nullable();
            $table->string('comment', 250)->comment('Краткое описание')->nullable();
            $table->integer('count_change_queue')->comment('Количество заявок до смены сектора')->default(0);
            $table->boolean('only_queue')->comment('0 - Отключено, 1 - Имеет приоритет перед остальными секторами')->default(0);
            $table->boolean('active')->comment('0 - Отключено, 1 - Включено')->default(0);
            $table->timestamps();
            
            $table->index('only_queue');
            $table->index('active');

            $table->index(['only_queue', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calls_sector_settings');
    }
}
