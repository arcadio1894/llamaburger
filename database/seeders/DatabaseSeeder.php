<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Para los nuevos seeders comentar los seeders existentes y ejecutar los seeders nuevos
         * con php artisan db:seed
         *
         */

        $this->call(DataCategoriesSeeder::class);
        $this->call(DataCouponSeeder::class);
        $this->call(DataGeneralSeeder::class);
        $this->call(DataMotivosSeeder::class);
        $this->call(DataPaymentMethodsSeeder::class);
        $this->call(DataProductsSeeder::class);
        $this->call(TypeSeeder::class);
        $this->call(ProductAndProductTypeSeeder::class);

        $this->call(DataRewardSeeder::class);
        $this->call(DataSubmotivosSeeder::class);

    }
}
