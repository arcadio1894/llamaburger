<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadosToComandasTable extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE comandas
            MODIFY estado ENUM('borrador','enviada','cocinando','lista','servida','cancelada')
            NOT NULL DEFAULT 'borrador'
        ");
    }

    public function down()
    {
        // Regrésalo a tu lista anterior de estados
        DB::statement("
            ALTER TABLE comandas
            MODIFY estado ENUM('borrador','enviada','servida','cancelada')
            NOT NULL DEFAULT 'borrador'
        ");
    }
}
