<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DataProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'code' => 'H-00001',
            'full_name' => 'Hamburguesa Clásica de la Casa',
            'description' => 'Jugosa carne de res 100% a la parrilla, queso cheddar, lechuga fresca, tomate y nuestra salsa especial, servida en pan artesanal.',
            'stock_current' => 50,
            'unit_price' => 18.50,
            'image' => 'hamburguesa_clasica.webp',
            'category_id' => 1, // Ajustar al ID real de "Hamburguesas Clásicas"
            'enable_status' => 1
        ]);

        Product::create([
            'code' => 'H-00002',
            'full_name' => 'Hamburguesa Gourmet BBQ Bacon',
            'description' => 'Carne de res jugosa cubierta con salsa barbacoa, queso gouda, tocino ahumado, cebolla caramelizada y pan brioche tostado.',
            'stock_current' => 40,
            'unit_price' => 24.00,
            'image' => 'hamburguesa_bbq_bacon.webp',
            'category_id' => 2, // Ajustar al ID real de "Hamburguesas Especiales"
            'enable_status' => 1
        ]);

    }
}
