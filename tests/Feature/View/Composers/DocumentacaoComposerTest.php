<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Documentacao;
use App\View\Composers\DocumentacaoComposer;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

 // Inválido
 test('retorna o link padrão se a rota não for encontrado no banco de dados', function () {
     $composer = new DocumentacaoComposer();

     $view = $this->spy(View::class);

     $composer->compose($view);

     $view
        ->shouldHaveReceived('with')
        ->with(['doc_link' => config('app.doc_link_default')])
        ->once();
 });

 test('retorna o link padrão se a rota for encontrada no banco de dados, mas sem o link para a documentção definido', function () {
     Documentacao::factory()->create([
        'app_link' => 'administracao.log.index',
        'doc_link' => null,
    ]);

     Route::shouldReceive('currentRouteName')
    ->once()
    ->andReturn('administracao.log.index');

     $composer = new DocumentacaoComposer();

     $view = $this->spy(View::class);

     $composer->compose($view);

     $view
        ->shouldHaveReceived('with')
        ->with(['doc_link' => config('app.doc_link_default')])
        ->once();
 });

// Caminho feliz
 test('retorna o link para a documentação da rota se essa estiver cadastrada no banco de dados e possuir o link para a documentação também registrado', function () {
     Documentacao::factory()->create([
        'app_link' => 'administracao.log.index',
        'doc_link' => 'http://foo.com',
    ]);

     Route::shouldReceive('currentRouteName')
    ->once()
    ->andReturn('administracao.log.index');

     $composer = new DocumentacaoComposer();

     $view = $this->spy(View::class);

     $composer->compose($view);

     $view
        ->shouldHaveReceived('with')
        ->with(['doc_link' => 'http://foo.com'])
        ->once();
 });
