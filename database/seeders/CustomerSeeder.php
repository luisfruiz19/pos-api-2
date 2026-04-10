<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Crear clientes usando la factory
        Customer::factory()->count(15)->create();

        $this->command->info('✅ CustomerSeeder: 15 clientes creados.');
    }
}
