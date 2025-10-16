<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersForBillingData extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Eliminar el campo 'numero' actual si es integer
            $table->dropColumn('numero');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Volver a agregar 'numero' como string
            $table->string('numero', 8)->nullable();

            // Nuevos campos para datos de facturación del cliente
            $table->string('nombre_cliente')->nullable();                  // Nombre o razón social
            $table->string('tipo_documento_cliente', 2)->nullable();       // '1' = DNI, '6' = RUC
            $table->string('numero_documento_cliente', 15)->nullable();    // DNI o RUC
            $table->string('direccion_cliente')->nullable();               // Dirección fiscal
            $table->string('email_cliente')->nullable();                   // Email si se desea enviar el comprobante
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revertir los cambios si es necesario
            $table->dropColumn([
                'numero',
                'nombre_cliente',
                'tipo_documento_cliente',
                'numero_documento_cliente',
                'direccion_cliente',
                'email_cliente',
            ]);

            // Volver a agregar 'numero' como integer (solo si quieres revertir)
            $table->integer('numero')->nullable();
        });
    }
}
