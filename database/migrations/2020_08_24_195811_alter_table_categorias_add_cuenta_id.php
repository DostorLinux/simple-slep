<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCategoriasAddCuentaId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categoria', function (Blueprint $table) {
            $table->unsignedInteger('cuenta_id')->after('icon_ref')->nullable();
            $table->foreign('cuenta_id', 'fk_categoria_cuenta1')
                ->references('id')
                ->on('cuenta')
                ->onDelete('set null')
                ->onUpdate('CASCADE');
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
        Schema::table('categoria', function (Blueprint $table) {
            $table->dropColumn('cuenta_id');
        });
    }
}
