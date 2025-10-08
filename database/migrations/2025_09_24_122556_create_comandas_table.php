<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComandasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comandas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atencion_id')->constrained('atenciones');
            $table->unsignedSmallInteger('numero')->default(1); // secuencia por atención
            $table->enum('estado', ['borrador','enviada','servida','cancelada'])->default('borrador');
            $table->decimal('total', 10, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['atencion_id','numero']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comandas');
    }
}
