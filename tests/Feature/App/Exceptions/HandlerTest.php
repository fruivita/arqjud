<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;

// Caminho feliz
test('renderiza o componente correto, bem como disponibiliza o status do erro', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::Login(Usuario::factory()->create());

    concederPermissao(Permissao::LOG_VIEW);

    get(route('atendimento.guia.show', 10))
        ->assertNotFound()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Error')
                ->whereAll([
                    'status' => 404,
                    'link' => route('home.show'),
                ])
        );
});
