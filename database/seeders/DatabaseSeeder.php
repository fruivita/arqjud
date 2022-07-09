<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * @see https://laravel.com/docs/9.x/seeding
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ConfiguracaoSeeder::class,
            LotacaoSeeder::class,
            PermissaoSeeder::class,
            PerfilSeeder::class,
            PerfilPermissaoSeeder::class,
        ]);
    }
}
