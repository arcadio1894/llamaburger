<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceCountersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('invoice_counters')->upsert([
            ['tipo'=>'boleta','serie'=>'B001','next_number'=>1],
            ['tipo'=>'factura','serie'=>'F001','next_number'=>1],
        ], ['tipo'], ['serie','next_number']);
    }
}
