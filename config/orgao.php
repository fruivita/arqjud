<?php

/**
 * @see https://laravel.com/docs/9.x/configuration
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Poder
    |--------------------------------------------------------------------------
    |
    | Poder estatal ao qual o órgão está vinculado.
    |
    | Ex.: Poder Judiciário
    |
    */
    'poder' => env('PODER'),

    /*
    |--------------------------------------------------------------------------
    | Especialidade
    |--------------------------------------------------------------------------
    |
    | Especialidade ou ramo de atividade do órgão.
    |
    | Ex.: Justiça Federal
    |
    */
    'especialidade' => env('ESPECIALIDADE'),

    /*
    |--------------------------------------------------------------------------
    | Nome
    |--------------------------------------------------------------------------
    |
    | Nome do órgão propriamente dito.
    |
    | Ex.: Seção Judiciária do Espírito Santo
    |
    */
    'nome' => env('NOME'),

    /*
    |--------------------------------------------------------------------------
    | Sigla
    |--------------------------------------------------------------------------
    |
    | Sigla do órgão.
    |
    | Ex.: SJES
    |
    */
    'sigla' => env('SIGLA'),

    /*
    |--------------------------------------------------------------------------
    | Arquivo corporativo
    |--------------------------------------------------------------------------
    |
    | Caminho completo para o arquivo com a estrutura corporativa do órgão.
    |
    */
    'arquivo_corporativo' => env('ARQUIVO_CORPORATIVO'),
];
