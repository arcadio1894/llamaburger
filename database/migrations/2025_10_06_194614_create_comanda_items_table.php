<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComandaItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comanda_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comanda_id');
            $table->unsignedBigInteger('product_id');
            $table->string('nombre', 150);                 // snapshot del nombre
            $table->decimal('precio_unit', 10, 2);         // snapshot del precio
            $table->integer('cantidad')->default(1);
            $table->enum('estado', ['pendiente','cocinando','listo','servido','anulado'])->default('pendiente');
            $table->json('opciones')->nullable();          // para toppings / combos
            $table->timestamps();

            $table->foreign('comanda_id')->references('id')->on('comandas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comanda_items');
    }
}
