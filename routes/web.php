<?php

use App\Enums\Policy;
use App\Http\Controllers\Cadastro\Localidade\LocalidadeController;
use App\Http\Controllers\HomeController;
use App\Models\Localidade;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return inertia('Autenticacao/Login');
    })->name('login');

    Route::post('/', [AuthenticatedSessionController::class, 'store'])->middleware(['throttle:login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('home')->name('home.')->group(function () {
        Route::get('/', [HomeController::class, 'show'])->name('show');
        Route::post('/', [HomeController::class, 'show']);
    });

    Route::prefix('cadastro')->name('cadastro.')->group(function () {
        Route::prefix('localidade')->name('localidade.')->group(function () {
            Route::get('/', [LocalidadeController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Localidade::class);
            Route::get('create', [LocalidadeController::class, 'create'])->name('create')->can(Policy::Create->value, Localidade::class);
            Route::post('/', [LocalidadeController::class, 'store'])->name('store')->can(Policy::Create->value, Localidade::class);
            Route::get('{localidade}/edit', [LocalidadeController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Localidade::class);
            Route::patch('{localidade}', [LocalidadeController::class, 'update'])->name('update')->can(Policy::Update->value, Localidade::class);
            Route::delete('{localidade}', [LocalidadeController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'localidade');
        });
    });
});
