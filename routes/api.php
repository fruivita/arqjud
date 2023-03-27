<?php

use App\Http\Controllers\Api\Movimentacao\ProcessoMovimentavelController;
use App\Http\Controllers\Api\Solicitacao\AutorizadaParaRecebedorController;
use App\Http\Controllers\Api\Solicitacao\ProcessoDisponivelController;
use App\Http\Controllers\Api\Solicitacao\SolicitanteController;
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
    Route::prefix('movimentacao')->name('movimentacao.')->group(function () {
        Route::prefix('processo')->name('processo.')->group(function () {
            Route::post('show', [ProcessoMovimentavelController::class, 'show'])->name('show');
        });
    });

    Route::prefix('solicitacao')->name('solicitacao.')->group(function () {
        Route::prefix('solicitante')->name('solicitante.')->group(function () {
            Route::post('/', [SolicitanteController::class, 'show'])->name('show');
        });

        Route::prefix('processo')->name('processo.')->group(function () {
            Route::post('/', [ProcessoDisponivelController::class, 'show'])->name('show');
        });

        Route::prefix('entregas-autorizadas')->name('entregas-autorizadas.')->group(function () {
            Route::post('/', [AutorizadaParaRecebedorController::class, 'show'])->name('show');
        });
    });
});
