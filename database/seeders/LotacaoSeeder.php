<?php

namespace Database\Seeders;

use App\Models\Lotacao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/9.x/seeding
 */
class LotacaoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('lotacoes')->insert([
            [
                'id' => Lotacao::SEM_LOTACAO,
                'nome' => __('Sem lotação'),
                'sigla' => __('Sem lotação'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
