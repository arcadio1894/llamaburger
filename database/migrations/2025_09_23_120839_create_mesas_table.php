<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMesasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sala_id')->constrained('salas');
            $table->string('nombre', 120);
            $table->enum('estado', ['libre','ocupada'])->default('libre');
            $table->text('descripcion')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Único: nombre por sala (excluyendo soft deletes se maneja en validación)
            $table->unique(['sala_id', 'nombre']);
            $table->index(['sala_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mesas');
    }
}
