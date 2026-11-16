<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleUserSeeder::class);

        // Data contoh hanya untuk development (PRD §6.3).
        if (! app()->isProduction()) {
            $this->call(ContentSeeder::class);
        }
    }
}
