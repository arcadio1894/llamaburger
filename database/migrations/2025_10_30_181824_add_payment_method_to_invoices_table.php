<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            //'payment_method_id',
            //        'payment_amount',
            //        'payment_code',
            $table->decimal('payment_amount', 10, 2)->nullable(); // Monto con el que se paga
            $table->string('payment_code')->nullable(); // Codigo de yape o plin
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('set null'); // Relación con payment_methods

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
}
