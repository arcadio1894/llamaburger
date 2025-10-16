<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoToAtencionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atenciones', function (Blueprint $table) {
            $table->enum('tipo', ['mesa','externo'])->default('mesa')->after('id');
            $table->unsignedBigInteger('mesa_id')->nullable()->change();
            $table->unsignedBigInteger('mozo_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atenciones', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
}
