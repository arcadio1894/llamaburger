<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceCountersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_counters', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['boleta','factura'])->unique(); // un contador por tipo
            $table->string('serie', 10);         // p.e. F001/B001
            $table->unsignedBigInteger('next_number')->default(1);
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
        Schema::dropIfExists('invoice_counters');
    }
}
