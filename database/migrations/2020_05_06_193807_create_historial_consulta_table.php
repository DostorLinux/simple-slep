<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistorialConsultaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_consulta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_agent',255)->nullable();
            $table->integer('file_id')->unsigned();
            $table->foreign('file_id')->references('id')->on('file');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_consulta');
    }
}
