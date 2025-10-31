<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateMetodoEnumInPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE payments MODIFY metodo ENUM('efectivo', 'yape_plin', 'pos') NOT NULL");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE payments MODIFY metodo ENUM('efectivo', 'tarjeta', 'yape', 'plin', 'trans') NOT NULL");

    }
}
