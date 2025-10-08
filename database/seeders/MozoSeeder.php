<?php

namespace Database\Seeders;

use App\Models\Mozo;
use App\Models\User;
use Illuminate\Database\Seeder;

class MozoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Opcional: enlazar por email si ya tienes estos usuarios creados
        $map = [
            ['nombre' => 'Juan Pérez',  'email' => 'jorge@gmail.com'],
        ];

        foreach ($map as $row) {
            $userId = null;
            if (!empty($row['email'])) {
                $userId = optional(User::where('email', $row['email'])->first())->id;
            }

            Mozo::firstOrCreate(
                ['nombre' => $row['nombre']],
                ['activo' => true, 'user_id' => $userId]
            );
        }
    }
}
