<?php

use App\Models\Caixa;
use App\Models\Guia;
use App\Models\Lotacao;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Models\VolumeCaixa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User;
use function Pest\Faker\faker;
use function Pest\Laravel\post;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(
    \Tests\TestCase::class,
    RefreshDatabase::class,
    AdditionalAssertions::class,
)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Configura o servidor LDAP fake para ser autenticado utilizando o
 * **samaccountname** informado.
 *
 * @param  string  $samaccountname
 * @return void
 */
function actingAs(string $samaccountname)
{
    $fake_ldap = DirectoryEmulator::setup('ldap');

    $usuario_ldap = User::create([
        'samaccountname' => $samaccountname,
        'objectguid' => faker()->uuid(),
    ]);

    $fake_ldap->actingAs($usuario_ldap);
}

/**
 * Faz login na aplicação utilizando o **samaccountname** informado.
 *
 * Note que o usuário é primeiro criado no 'active directory' fake para então
 * ser autenticado. Ou seja, é necessário garantir que o usuário primeiro exita
 * no LDAP server para então ser autenticado.
 *
 * @param  string  $samaccountname
 * @return \App\Models\Usuario|null
 */
function login(string $samaccountname = 'foo')
{
    actingAs($samaccountname);

    post(route('login'), [
        'username' => $samaccountname,
        'password' => 'secret',
    ]);

    $usuario = usuarioAutenticado();
    $usuario->lotacao()->associate(Lotacao::factory()->create());
    $usuario->save();

    return $usuario;
}

/**
 * @return \App\Models\Usuario|null
 */
function usuarioAutenticado() // @phpstan-ignore-line
{
    return auth()->user(); // @phpstan-ignore-line
}

/**
 * @return void
 */
function logout()
{
    post(route('logout'));
}

/**
 * Concede a permissão informada ao usuário autenticado.
 *
 * @param  array|string  $slugs
 * @return void
 */
function concederPermissao(mixed $slugs)
{
    $permissoes = collect()->wrap($slugs)->map(function (string $slug) {
        $permissao = Permissao::where('slug', $slug)->firstOr(
            fn () => Permissao::factory()->create(['slug' => $slug])
        );

        return $permissao->id;
    });

    usuarioAutenticado()
        ->refresh()
        ->perfil
        ->permissoes()
        ->attach($permissoes);
}

/**
 * Remove a permissão do usuário autenticado.
 *
 * @param  string  $slug
 * @return void
 */
function revogaPermissao(string $slug)
{
    usuarioAutenticado()
        ->perfil
        ->permissoes()
        ->detach(Permissao::firstWhere('slug', $slug)->id);
}

// Helpers APIs
/**
 * @param  \App\Models\Lotacao|\FruiVita\Corporativo\Models\Lotacao  $lotacao
 * @return array<string, mixed>
 */
function lotacaoApi(mixed $lotacao)
{
    return [
        'id' => $lotacao->id,
        'nome' => $lotacao->nome,
        'sigla' => mb_strtoupper($lotacao->sigla),
        'administravel' => $lotacao->administravel,
        'lotacao_pai_id' => $lotacao->lotacao_pai,
    ];
}

/**
 * @param  \App\Models\Caixa  $caixa
 * @return array<string, mixed>
 */
function caixaApi(Caixa $caixa)
{
    return [
        'id' => $caixa->id,
        'numero' => $caixa->numero,
        'ano' => $caixa->ano,
        'guarda_permanente' => $caixa->guarda_permanente,
        'complemento' => $caixa->complemento,
        'descricao' => $caixa->descricao,
        'prateleira_id' => $caixa->prateleira_id,
        'localidade_criadora_id' => $caixa->localidade_criadora_id,
    ];
}

/**
 * @param  \App\Models\Processo  $processo
 * @return array<string, mixed>
 */
function processoApi(Processo $processo)
{
    return [
        'id' => $processo->id,
        'numero' => $processo->numero,
        'numero_antigo' => $processo->numero_antigo,
        'arquivado_em' => $processo->arquivado_em->format('d-m-Y'),
        'guarda_permanente' => $processo->guarda_permanente,
        'qtd_volumes' => $processo->qtd_volumes,
        'descricao' => $processo->descricao,
        'volume_caixa_id' => $processo->volume_caixa_id,
        'processo_pai_id' => $processo->processo_pai_id,
    ];
}

/**
 * @param  \App\Models\Solicitacao  $solicitacao
 * @return array<string, mixed>
 */
function solicitacaoApi(Solicitacao $solicitacao)
{
    return [
        'id' => $solicitacao->id,
        'solicitada_em' => $solicitacao->solicitada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
        'entregue_em' => $solicitacao->entregue_em?->tz(config('app.tz'))->format('d-m-Y H:i:s'),
        'devolvida_em' => $solicitacao->devolvida_em?->tz(config('app.tz'))->format('d-m-Y H:i:s'),
        'por_guia' => $solicitacao->por_guia,
        'descricao' => $solicitacao->descricao,
        'status' => $solicitacao->status,
        'processo_id' => $solicitacao->processo_id,
        'solicitante_id' => $solicitacao->solicitante_id,
        'recebedor_id' => $solicitacao->recebedor_id,
        'remetente_id' => $solicitacao->remetente_id,
        'rearquivador_id' => $solicitacao->rearquivador_id,
        'lotacao_destinataria_id' => $solicitacao->lotacao_destinataria_id,
        'guia_id' => $solicitacao->guia_id,
    ];
}

/**
 * @param  \App\Models\Usuario  $usuario
 * @return array<string, mixed>
 */
function usuarioApi(Usuario $usuario)
{
    return [
        'id' => $usuario->id,
        'matricula' => $usuario->matricula,
        'username' => $usuario->username,
        'nome' => $usuario->nome,
        'email' => $usuario->email,
        'status' => $usuario->habilitado() ? __('completo') : __('incompleto'),
        'funcao_confianca_id' => $usuario->funcao_confianca_id,
        'perfil_id' => $usuario->perfil_id,
        'lotacao_id' => $usuario->lotacao_id,
        'cargo_id' => $usuario->cargo_id,
    ];
}

/**
 * @param  \App\Models\VolumeCaixa  $volume
 * @return array<string, mixed>
 */
function volumeApi(VolumeCaixa $volume)
{
    return [
        'id' => $volume->id,
        'numero' => $volume->numero,
        'descricao' => $volume->descricao,
        'caixa_id' => $volume->caixa_id,
    ];
}

/**
 * @param  \Illuminate\Database\Eloquent\Collection  $volumes
 * @return array
 */
function volumesApi(Collection $volumes)
{
    return $volumes
        ->map(fn (VolumeCaixa $volume) => volumeApi($volume)) // @phpstan-ignore-line
        ->toArray();
}

/**
 * @param  \App\Models\Guia  $guia
 * @return array
 */
function guiaApi(Guia $guia)
{
    return [
        'id' => $guia->id,
        'numero' => $guia->numero,
        'ano' => $guia->ano,
        'gerada_em' => $guia->gerada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
        'remetente' => [
            'username' => $guia->remetente['username'],
            'nome' => $guia->remetente['nome'],
        ],
        'recebedor' => [
            'username' => $guia->recebedor['username'],
            'nome' => $guia->recebedor['nome'],
        ],
        'lotacao_destinataria' => [
            'sigla' => str($guia->lotacao_destinataria['sigla'])->upper()->toString(),
            'nome' => $guia->lotacao_destinataria['nome'],
        ],
        'processos' => $guia->processos->transform(function ($processo) {
            $processo['numero'] = cnj($processo['numero']);

            return $processo;
        })->toArray(),
    ];
}
