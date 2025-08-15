<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class DataCouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Coupon::create([
            'name' => 'MiPrimeraBurger',
            'description' => "Codigo de promoción por la primera pizza",
            'status' => 'active',
            'amount' => 0.00,
            'percentage' => 50,
            'type' => 'detail'
        ]);

        Coupon::create([
            'name' => 'UnMesConLlamaBurger',
            'description' => "Codigo de promoción por el primer mes",
            'status' => 'active',
            'amount' => 10.00,
            'percentage' => 0.00,
            'type' => 'total'
        ]);
    }
}
