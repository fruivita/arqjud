<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Sala\JoinLocalidade;
use App\Models\Sala;
use App\Models\Localidade;
use App\Models\Predio;
use App\Models\Andar;
use Illuminate\Pipeline\Pipeline;

// Caminho feliz
test('join da sala até a localidade', function () {
    $localidade = Localidade::factory()->create();
    $predio = Predio::factory()->for($localidade, 'localidade')->create();
    $andar = Andar::factory()->for($predio, 'predio')->create();
    Sala::factory()->for($andar, 'andar')->create();

    $sala = app(Pipeline::class)
        ->send(Sala::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($sala->first())->toBe($localidade->nome);
});
