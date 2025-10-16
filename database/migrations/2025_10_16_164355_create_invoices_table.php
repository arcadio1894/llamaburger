<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atencion_id')->nullable()->constrained()->nullOnDelete(); // mesa o externo
            $table->foreignId('customer_id')->nullable()->constrained('clientes')->nullOnDelete();

            // tipo y numeración
            $table->enum('tipo', ['boleta','factura','ticket'])->default('ticket'); // 'ticket' si no piden comprobante fiscal
            $table->string('serie', 10)->nullable();  // p.e. F001, B001
            $table->unsignedBigInteger('numero')->nullable(); // correlativo

            // datos de facturación "congelados" (snapshot)
            $table->string('cliente_nombre', 200)->nullable();
            $table->string('cliente_doc_tipo', 10)->nullable();   // DNI, RUC, CE, PAS
            $table->string('cliente_doc_num', 20)->nullable();
            $table->string('cliente_direccion', 255)->nullable();

            // montos
            $table->decimal('op_gravada', 12, 2)->default(0);   // base imponible
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('op_exonerada', 12, 2)->default(0);
            $table->decimal('op_inafecta', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('moneda', 3)->default('PEN');

            // estado de emisión con facturador (si aplica)
            $table->enum('estado', ['borrador','emitido','anulado'])->default('emitido');
            $table->timestamp('issue_date')->useCurrent();
            $table->json('extra')->nullable(); // respuesta de facturador, hash, cdr, etc.

            $table->timestamps();

            $table->index(['tipo','serie','numero']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
