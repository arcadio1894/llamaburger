<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // snapshot del producto/ítem
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('descripcion', 255);
            $table->decimal('cantidad', 12, 3)->default(1);
            $table->string('unidad', 10)->default('NIU');

            $table->decimal('valor_unitario', 12, 6)->default(0); // sin IGV
            $table->decimal('precio_unitario', 12, 6)->default(0); // con IGV
            $table->decimal('subtotal', 12, 2)->default(0); // sin IGV
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0); // con IGV

            $table->enum('afectacion', ['10','20','30','40','00'])->default('10'); // 10: gravado, 20: exonerado, etc.
            $table->json('extra')->nullable();

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
        Schema::dropIfExists('invoice_items');
    }
}
