<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DataCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::create([
            'name' => 'Hamburguesas Clásicas',
            'description' => 'Recetas tradicionales con el sabor auténtico de la parrilla.'
        ]);

        Category::create([
            'name' => 'Hamburguesas Especiales',
            'description' => 'Combinaciones creativas y sabores únicos para los más exigentes.'
        ]);
    }
}
