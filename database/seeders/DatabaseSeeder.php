<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Catálogo para probar POST /api/v1/pedidos desde Postman.
        \Illuminate\Support\Facades\DB::table('productos')->insert([
            ['id' => 1, 'nombre' => 'Torta de chocolate', 'precio' => '12.50'],
            ['id' => 4, 'nombre' => 'Caja de chocolates', 'precio' => '15.00'],
        ]);
    }
}
