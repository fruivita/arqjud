<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use JMac\Testing\Traits\AdditionalAssertions;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use Tests\CreatesApplication;
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
    TestCase::class,
    CreatesApplication::class,
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
        'cn' => $samaccountname,
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

    return usuarioAutenticado();
}

/**
 * @return \App\Models\Usuario|null
 */
function usuarioAutenticado()
{
    return auth()->user();
}

/**
 * @return void
 */
function logout()
{
    post(route('logout'));
}
