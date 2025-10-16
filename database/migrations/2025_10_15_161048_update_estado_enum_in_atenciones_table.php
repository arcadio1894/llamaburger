<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateEstadoEnumInAtencionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 👉 Aquí va el ALTER TABLE con DB::statement
        DB::statement("
            ALTER TABLE atenciones 
            MODIFY COLUMN estado ENUM('abierta','por_pagar','cerrada','anulada') 
            DEFAULT 'abierta'
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            ALTER TABLE atenciones 
            MODIFY COLUMN estado ENUM('abierta','cerrada') 
            DEFAULT 'abierta'
        ");
    }
}
