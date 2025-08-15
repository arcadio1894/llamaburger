<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            ['name' => 'Normal', 'size' => '12cm', 'price' => 15.00],
        ];

        foreach ($types as $type) {
            Type::create($type);
        }
    }
}
