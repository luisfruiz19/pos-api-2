<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()->count(10)->create();

        $this->command->info('✅ Categorías creadas exitosamente');
    }
}
