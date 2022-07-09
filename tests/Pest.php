<?php

use App\Models\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User as UsuarioLdap;
use function Pest\Faker\faker;
use function Pest\Laravel\post;
use Tests\CreatesApplication;

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
    TestCase::class,
    CreatesApplication::class,
    RefreshDatabase::class
)->in('Feature', 'Unit');

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

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

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
 * Faz login na aplicação utilizando o **samaccountname** informado.
 *
 * Notar que o usuário é primeiro criado no 'active directory' para então ser
 * autenticado.
 *
 * @param string $samaccountname
 *
 * @return \App\Models\Usuario|null
 */
function login(string $samaccountname)
{
    $fake_ldap = DirectoryEmulator::setup('ldap');

    $usuario_ldap = UsuarioLdap::create([
        'cn' => $samaccountname . ' bar baz',
        'samaccountname' => $samaccountname,
        'objectguid' => faker()->uuid(),
    ]);

    $fake_ldap->actingAs($usuario_ldap);

    post(route('login'), [
        'username' => $usuario_ldap->samaccountname[0], // @phpstan-ignore-line
        'password' => 'secret',
    ]);

    return usuarioAutenticado();
}

/**
 * @return \App\Models\Usuario|null
 */
function usuarioAutenticado()// @phpstan-ignore-line
{
    return Auth::user(); // @phpstan-ignore-line
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
 * @param int $id_permissao
 *
 * @return void
 */
function concederPermissao(int $id_permissao)
{
    $permissao = Permissao::where('id', $id_permissao)->firstOr(function () use ($id_permissao) {
        return Permissao::factory()->create(['id' => $id_permissao]);
    });

    usuarioAutenticado()
        ->refresh()
        ->perfil
        ->permissoes()
        ->attach($permissao);
}

/**
 * Remove a permissão do usuário autenticado.
 *
 * @param int $id_permissao
 *
 * @return void
 */
function revogaPermissao(int $id_permissao)
{
    usuarioAutenticado()
    ->perfil
    ->permissoes()
    ->detach($id_permissao);
}
