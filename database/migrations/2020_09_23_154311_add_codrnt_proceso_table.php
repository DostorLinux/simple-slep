<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodrntProcesoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // issue https://git.gob.cl/simple/simple/issues/622

        Schema::table('proceso', function (Blueprint $table) {
        $table->string('nombre_frontend', 255)->after('nombre')->nullable();
        $table->string('codigo_rnt', 128)->after('nombre_frontend')->nullable();
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
            $table->dropColumn('nombre_frontend');
            $table->dropColumn('codigo_rnt');
        });
    }
}
