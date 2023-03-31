<?php

use App\Models\Caixa;
use App\Models\Guia;
use App\Models\Lotacao;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use JMac\Testing\Traits\AdditionalAssertions;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User;
use function Pest\Faker\faker;
use function Pest\Laravel\post;
use Spatie\SimpleExcel\SimpleExcelWriter;

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
 * **matricula** informado.
 *
 * @return void
 */
function actingAs(string $matricula)
{
    $fake_ldap = DirectoryEmulator::setup('ldap');

    $usuario_ldap = User::create([
        'matricula' => $matricula,
        'objectguid' => faker()->uuid(),
    ]);

    $fake_ldap->actingAs($usuario_ldap);
}

/**
 * Faz login na aplicação utilizando a **matrícula** informada.
 *
 * Note que o usuário é primeiro criado no 'active directory' fake para então
 * ser autenticado. Ou seja, é necessário garantir que o usuário primeiro exita
 * no LDAP server para então ser autenticado.
 *
 * @return \App\Models\Usuario|null
 */
function login(string $matricula = 'foo999999')
{
    actingAs($matricula);

    post(route('login'), [
        'matricula' => $matricula,
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
        'tipo_processo_id' => $caixa->tipo_processo_id,
    ];
}

/**
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
        'vol_caixa_inicial' => $processo->vol_caixa_inicial,
        'vol_caixa_final' => $processo->vol_caixa_final,
        'descricao' => $processo->descricao,
        'caixa_id' => $processo->caixa_id,
        'processo_pai_id' => $processo->processo_pai_id,
    ];
}

/**
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
        'destino_id' => $solicitacao->destino_id,
        'guia_id' => $solicitacao->guia_id,
    ];
}

/**
 * @return array<string, mixed>
 */
function usuarioApi(Usuario $usuario)
{
    return [
        'id' => $usuario->id,
        'matricula' => $usuario->matricula,
        'nome' => $usuario->nome,
        'email' => $usuario->email,
        'ultimo_login' => $usuario->ultimo_login?->tz(config('app.tz'))->format('d-m-Y H:i:s'),
        'ip' => $usuario->ip,
        'status' => $usuario->habilitado() ? __('completo') : __('incompleto'),
        'funcao_confianca_id' => $usuario->funcao_confianca_id,
        'perfil_id' => $usuario->perfil_id,
        'lotacao_id' => $usuario->lotacao_id,
        'cargo_id' => $usuario->cargo_id,
    ];
}

/**
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
            'matricula' => $guia->remetente['matricula'],
            'nome' => $guia->remetente['nome'],
        ],
        'recebedor' => [
            'matricula' => $guia->recebedor['matricula'],
            'nome' => $guia->recebedor['nome'],
        ],
        'destino' => [
            'sigla' => str($guia->destino['sigla'])->upper()->toString(),
            'nome' => $guia->destino['nome'],
        ],
        'processos' => $guia->processos->transform(function ($processo) {
            $processo['numero'] = cnj($processo['numero']);

            return $processo;
        })->toArray(),
    ];
}

/**
 * Headers para uso no arquivo de processos para importação.
 *
 * @return string[]
 */
function csvHeader()
{
    return [
        'Número do Processo',
        'Número do Processo Antigo',
        'Número Processo Pai',
        'Data arquivamento',
        'Qte de Volumes',
        'Número Caixa',
        'Tipo',
        'Ano Caixa',
        'Volume Inicial Caixa',
        'Volume Final Caixa',
        'Arquivo Permanente',
        'Localidade de Origem',
        'Localização',
        'Prédio',
        'Andar',
        'Sala',
        'Estante',
        'Prateleita',
        'Observação',
    ];
}

/**
 * Cria, no Storage, arquivos de processos no formato CSV para serem importados
 * utilizando o stub informado.
 *
 * @return void
 */
function criarArquivoProcesso(string $nome_arquivo, string $stub)
{
    $linhas = require __DIR__ . "/stubs/{$stub}.php";

    $dados = [];

    foreach ($linhas as $linha) {
        $dados[] = array_combine(csvHeader(), $linha);
    }

    SimpleExcelWriter::create(Storage::disk('processo')->path($nome_arquivo))
        ->addRows($dados);
}
