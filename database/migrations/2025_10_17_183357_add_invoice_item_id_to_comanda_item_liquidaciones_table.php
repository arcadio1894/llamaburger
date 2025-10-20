<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceItemIdToComandaItemLiquidacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comanda_item_liquidaciones', function (Blueprint $table) {
            $table->foreignId('invoice_item_id')
                ->nullable()
                ->constrained('invoice_items')
                ->nullOnDelete()
                ->after('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comanda_item_liquidaciones', function (Blueprint $table) {
            //
        });
    }
}
