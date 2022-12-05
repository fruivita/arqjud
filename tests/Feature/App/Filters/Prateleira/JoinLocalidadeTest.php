<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Prateleira\JoinLocalidade;
use App\Models\Andar;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Pipeline\Pipeline;

// Caminho feliz
test('join da prateleira até a localidade', function () {
    $localidade = Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()->hasPrateleiras(1))), 'andares'))
        ->create();

    $prateleira = app(Pipeline::class)
        ->send(Prateleira::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($prateleira->first())->toBe($localidade->nome);
});
