<?php

use App\Http\Controllers\Api\Caixa\CaixaController;
use App\Http\Controllers\Api\Processo\ProcessoController;
use App\Http\Controllers\Api\Solicitacao\ProcessoDisponivelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::prefix('processo')->name('processo.')->group(function () {
        Route::post('show', [ProcessoController::class, 'show'])->name('show');
    });

    Route::prefix('caixa')->name('caixa.')->group(function () {
        Route::post('show', [CaixaController::class, 'show'])->name('show');
    });

    Route::prefix('solicitacao')->name('solicitacao.')->group(function () {
        Route::prefix('processo')->name('processo.')->group(function () {
            Route::post('/', [ProcessoDisponivelController::class, 'show'])->name('show');
        });
    });
});
