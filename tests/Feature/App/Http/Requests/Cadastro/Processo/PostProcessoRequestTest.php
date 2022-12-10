<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Processo\PostProcessoRequest;
use App\Models\Permissao;
use App\Models\Processo;
use App\Rules\NumeroProcesso;
use App\Rules\NumeroProcessoCNJ;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->request = new PostProcessoRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

test('na atualização, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    $processo = Processo::factory()->create();

    login();

    // como há um processo no request, será uma atualização
    $this->request->processo = $processo;

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $this->request->caixa_id = 10;

    $this->assertExactValidationRules([
        'volume_caixa_id' => [
            'bail',
            'required',
            'integer',
            'exists:volumes_caixa,id',
        ],

        'processo_pai_numero' => [
            'bail',
            'nullable',
            'string',
            'max:25',
            new NumeroProcessoCNJ(),
            'exists:processos,numero',
        ],

        'numero' => [
            'bail',
            'required',
            'string',
            'max:25',
            new NumeroProcessoCNJ(),
            'unique:processos,numero',
        ],

        'numero_antigo' => [
            'bail',
            'nullable',
            'string',
            new NumeroProcesso(),
            'unique:processos,numero_antigo',
        ],

        'arquivado_em' => [
            'bail',
            'required',
            'date_format:d-m-Y',
            'after_or_equal:01-01-1900',
            'before_or_equal:' . now()->format('d-m-Y'),
        ],

        'qtd_volumes' => [
            'bail',
            'required',
            'integer',
            'between:1,9999',
        ],

        'descricao' => [
            'bail',
            'nullable',
            'string',
            'between:1,255',
        ],
    ], $this->request->rules());
});

test('rules estão definidas no form request para a atualização do registro', function () {
    $processo = Processo::factory()->create();

    // como há um processo no request, será uma atualização
    $this->request->processo = $processo;

    $this->assertExactValidationRules([
        'volume_caixa_id' => [
            'bail',
            'nullable',
            'integer',
            'exists:volumes_caixa,id',
        ],

        'processo_pai_numero' => [
            'bail',
            'nullable',
            'string',
            'max:25',
            new NumeroProcessoCNJ(),
            'exists:processos,numero',
        ],

        'numero' => [
            'bail',
            'required',
            'string',
            'max:25',
            new NumeroProcessoCNJ(),
            "unique:processos,numero,{$processo->id}",
        ],

        'numero_antigo' => [
            'bail',
            'nullable',
            'string',
            new NumeroProcesso(),
            "unique:processos,numero_antigo,{$processo->id}",
        ],

        'arquivado_em' => [
            'bail',
            'required',
            'date_format:d-m-Y',
            'after_or_equal:01-01-1900',
            'before_or_equal:' . now()->format('d-m-Y'),
        ],

        'qtd_volumes' => [
            'bail',
            'required',
            'integer',
            'between:1,9999',
        ],

        'descricao' => [
            'bail',
            'nullable',
            'string',
            'between:1,255',
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'volume_caixa_id' => __('Volume da caixa'),
        'processo_pai_numero' => __('Processo pai'),
        'numero' => __('Número do processo'),
        'numero_antigo' => __('Número antigo do processo'),
        'arquivado_em' => __('Data de arquivamento'),
        'qtd_volumes' => __('Qtd volumes'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::PROCESSO_CREATE);

    expect($this->request->authorize())->toBeTrue();
});

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::PROCESSO_UPDATE);

    $processo = Processo::factory()->create();
    // como há um processo no request, será uma atualização
    $this->request->processo = $processo;

    expect($this->request->authorize())->toBeTrue();
});
