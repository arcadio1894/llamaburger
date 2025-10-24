<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKitchenTimingToComandasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->timestamp('started_cooking_at')->nullable()->after('sent_to_kitchen_at');
            $table->unsignedSmallInteger('estimated_minutes')->nullable()->after('started_cooking_at');
            $table->timestamp('estimated_ready_at')->nullable()->after('estimated_minutes');
            $table->timestamp('ready_at')->nullable()->after('estimated_ready_at');
            $table->timestamp('delivered_at')->nullable()->after('ready_at');

            $table->index(['estado']);
            $table->index(['started_cooking_at']);
            $table->index(['estimated_ready_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->dropIndex(['comandas_estado_index']);
            $table->dropIndex(['comandas_started_cooking_at_index']);
            $table->dropIndex(['comandas_estimated_ready_at_index']);
            $table->dropColumn([
                'started_cooking_at',
                'estimated_minutes',
                'estimated_ready_at',
                'ready_at',
                'delivered_at',
            ]);
        });
    }
}
