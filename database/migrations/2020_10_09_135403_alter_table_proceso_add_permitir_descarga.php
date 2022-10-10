<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProcesoAddPermitirDescarga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proceso', function (Blueprint $table) {
            $table->boolean('descarga_documentos')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proceso', function (Blueprint $table) {
            $table->dropColumn('descarga_documentos');
        });
    }
}
