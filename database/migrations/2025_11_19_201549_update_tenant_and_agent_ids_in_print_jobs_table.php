<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTenantAndAgentIdsInPrintJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            // 1. Crear columnas temporales para conversion
            $table->unsignedBigInteger('tenant_id_new')->nullable()->after('tenant_id');
            $table->unsignedBigInteger('agent_id_new')->nullable()->after('agent_id');
        });

        // 2. Convertir datos (si valores anteriores eran strings que representan números)
        DB::table('print_jobs')->get()->each(function ($row) {
            DB::table('print_jobs')
                ->where('id', $row->id)
                ->update([
                    'tenant_id_new' => is_numeric($row->tenant_id) ? (int)$row->tenant_id : null,
                    'agent_id_new'  => is_numeric($row->agent_id)  ? (int)$row->agent_id  : null,
                ]);
        });

        Schema::table('print_jobs', function (Blueprint $table) {
            // 3. Eliminar columnas viejas
            $table->dropColumn('tenant_id');
            $table->dropColumn('agent_id');

            // 4. Renombrar columnas nuevas
            $table->renameColumn('tenant_id_new', 'tenant_id');
            $table->renameColumn('agent_id_new',  'agent_id');
        });

        Schema::table('print_jobs', function (Blueprint $table) {
            // 5. Agregar claves foráneas
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['agent_id']);

            $table->string('tenant_id')->nullable()->change();
            $table->string('agent_id')->nullable()->change();
        });
    }
}
