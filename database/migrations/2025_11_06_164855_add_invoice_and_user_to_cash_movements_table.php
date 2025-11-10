<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceAndUserToCashMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            // Nuevas columnas (opcionales / podrían ser null)
            $table->unsignedBigInteger('invoice_id')->nullable()->after('order_id');
            $table->unsignedBigInteger('user_id')->nullable()->after('invoice_id');

            // Índices
            $table->index('invoice_id');
            $table->index('user_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['user_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['user_id']);

            $table->dropColumn(['invoice_id', 'user_id']);
        });
    }
}
