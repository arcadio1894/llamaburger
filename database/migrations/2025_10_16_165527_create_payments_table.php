<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('atencion_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('metodo', ['efectivo','tarjeta','yape','plin','transferencia','mixto','otro'])->default('efectivo');
            $table->decimal('monto', 12, 2);
            $table->string('moneda', 3)->default('PEN');

            // Para efectivo (opcional)
            $table->decimal('monto_recibido', 12, 2)->nullable();
            $table->decimal('vuelto', 12, 2)->nullable();

            $table->string('referencia', 100)->nullable(); // código operación, últimos 4 tarjeta, etc.
            $table->enum('estado', ['aplicado','anulado'])->default('aplicado');
            $table->timestamp('paid_at')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->json('extra')->nullable();

            $table->timestamps();

            $table->index(['atencion_id','invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
