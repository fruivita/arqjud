<?php

use App\Enums\Policy;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Administracao\Configuracao\ConfiguracaoLivewireUpdate;
use App\Http\Livewire\Administracao\Documentacao\DocumentacaoLivewireCreate;
use App\Http\Livewire\Administracao\Documentacao\DocumentacaoLivewireIndex;
use App\Http\Livewire\Administracao\Documentacao\DocumentacaoLivewireUpdate;
use App\Http\Livewire\Administracao\Importacao\ImportacaoLivewireCreate;
use App\Http\Livewire\Administracao\Log\LogLivewireIndex;
use App\Http\Livewire\Arquivamento\Cadastro\Andar\AndarLivewireCreate;
use App\Http\Livewire\Arquivamento\Cadastro\Andar\AndarLivewireIndex;
use App\Http\Livewire\Arquivamento\Cadastro\Andar\AndarLivewireUpdate;
use App\Http\Livewire\Arquivamento\Cadastro\Caixa\CaixaLivewireCreate;
use App\Http\Livewire\Arquivamento\Cadastro\Caixa\CaixaLivewireIndex;
use App\Http\Livewire\Arquivamento\Cadastro\Caixa\CaixaLivewireUpdate;
use App\Http\Livewire\Arquivamento\Cadastro\Estante\EstanteLivewireCreate;
use App\Http\Livewire\Arquivamento\Cadastro\Estante\EstanteLivewireIndex;
use App\Http\Livewire\Arquivamento\Cadastro\Estante\EstanteLivewireUpdate;
use App\Http\Livewire\Arquivamento\Cadastro\Localidade\LocalidadeLivewireCreate;
use App\Http\Livewire\Arquivamento\Cadastro\Localidade\LocalidadeLivewireIndex;
use App\Http\Livewire\Arquivamento\Cadastro\Localidade\LocalidadeLivewireUpdate;
use App\Http\Livewire\Arquivamento\Cadastro\Prateleira\PrateleiraLivewireCreate;
use App\Http\Livewire\Arquivamento\Cadastro\Prateleira\PrateleiraLivewireIndex;
use App\Http\Livewire\Arquivamento\Cadastro\Prateleira\PrateleiraLivewireUpdate;
use App\Http\Livewire\Arquivamento\Cadastro\Predio\PredioLivewireCreate;
use App\Http\Livewire\Arquivamento\Cadastro\Predio\PredioLivewireIndex;
use App\Http\Livewire\Arquivamento\Cadastro\Predio\PredioLivewireUpdate;
use App\Http\Livewire\Arquivamento\Cadastro\Sala\SalaLivewireCreate;
use App\Http\Livewire\Arquivamento\Cadastro\Sala\SalaLivewireIndex;
use App\Http\Livewire\Arquivamento\Cadastro\Sala\SalaLivewireUpdate;
use App\Http\Livewire\Autorizacao\Delegacao\DelegacaoLivewireIndex;
use App\Http\Livewire\Autorizacao\Perfil\PerfilLivewireIndex;
use App\Http\Livewire\Autorizacao\Perfil\PerfilLivewireUpdate;
use App\Http\Livewire\Autorizacao\Permissao\PermissaoLivewireIndex;
use App\Http\Livewire\Autorizacao\Permissao\PermissaoLivewireUpdate;
use App\Http\Livewire\Autorizacao\Usuario\UsuarioLivewireIndex;
use App\Http\Livewire\Teste\Simulacao\SimulacaoLivewireCreate;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Configuracao;
use App\Models\Documentacao;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use App\Models\Usuario;
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
        return view('login');
    })->name('login');

    Route::post('/', [AuthenticatedSessionController::class, 'store'])
        ->middleware(['throttle:login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('home', [HomeController::class, 'index'])->name('home');

    Route::prefix('autorizacao')->name('autorizacao.')->group(function () {
        Route::prefix('delegacao')->name('delegacao.')->group(function () {
            Route::get('/', DelegacaoLivewireIndex::class)->name('index')->can(Policy::DelegacaoViewAny->value);
        });

        Route::prefix('perfil')->name('perfil.')->group(function () {
            Route::get('/', PerfilLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Perfil::class);
            Route::get('edit/{perfil}', PerfilLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Perfil::class);
        });

        Route::prefix('permissao')->name('permissao.')->group(function () {
            Route::get('/', PermissaoLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Permissao::class);
            Route::get('edit/{permissao}', PermissaoLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Permissao::class);
        });

        Route::prefix('usuario')->name('usuario.')->group(function () {
            Route::get('/', UsuarioLivewireIndex::class)->name('index')->can(Policy::ViewAnyOrUpdate->value, Usuario::class);
        });
    });

    Route::prefix('administracao')->name('administracao.')->group(function () {
        Route::prefix('configuracao')->name('configuracao.')->group(function () {
            Route::get('edit', ConfiguracaoLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Configuracao::class);
        });

        Route::prefix('documentacao')->name('documentacao.')->group(function () {
            Route::get('/', DocumentacaoLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Documentacao::class);
            Route::get('create', DocumentacaoLivewireCreate::class)->name('create')->can(Policy::Create->value, Documentacao::class);
            Route::get('edit/{documentacao}', DocumentacaoLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Documentacao::class);
        });

        Route::prefix('importacao')->name('importacao.')->group(function () {
            Route::get('create', ImportacaoLivewireCreate::class)->name('create')->can(Policy::ImportacaoCreate->value);
        });

        Route::prefix('log')->name('log.')->group(function () {
            Route::get('/', LogLivewireIndex::class)->name('index')->can(Policy::LogViewAny->value);
        });
    });

    Route::prefix('arquivamento')->name('arquivamento.')->group(function () {
        Route::prefix('cadastro')->name('cadastro.')->group(function () {
            Route::prefix('caixa')->name('caixa.')->group(function () {
                Route::get('/', CaixaLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Caixa::class);
                Route::get('prateleira/{id}/create', CaixaLivewireCreate::class)->name('create')->can(Policy::Create->value, Caixa::class);
                Route::get('edit/{id}', CaixaLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Caixa::class);
            });

            Route::prefix('localidade')->name('localidade.')->group(function () {
                Route::get('/', LocalidadeLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Localidade::class);
                Route::get('create', LocalidadeLivewireCreate::class)->name('create')->can(Policy::Create->value, Localidade::class);
                Route::get('edit/{localidade}', LocalidadeLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Localidade::class);
            });

            Route::prefix('predio')->name('predio.')->group(function () {
                Route::get('/', PredioLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Predio::class);
                Route::get('localidade/{id}/create', PredioLivewireCreate::class)->name('create')->can(Policy::Create->value, Predio::class);
                Route::get('edit/{id}', PredioLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Predio::class);
            });

            Route::prefix('andar')->name('andar.')->group(function () {
                Route::get('/', AndarLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Andar::class);
                Route::get('predio/{id}/create', AndarLivewireCreate::class)->name('create')->can(Policy::Create->value, Andar::class);
                Route::get('edit/{id}', AndarLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Andar::class);
            });

            Route::prefix('sala')->name('sala.')->group(function () {
                Route::get('/', SalaLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Sala::class);
                Route::get('andar/{id}/create', SalaLivewireCreate::class)->name('create')->can(Policy::Create->value, Sala::class);
                Route::get('edit/{id}', SalaLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Sala::class);
            });

            Route::prefix('estante')->name('estante.')->group(function () {
                Route::get('/', EstanteLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Estante::class);
                Route::get('sala/{id}/create', EstanteLivewireCreate::class)->name('create')->can(Policy::Create->value, Estante::class);
                Route::get('edit/{id}', EstanteLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Estante::class);
            });

            Route::prefix('prateleira')->name('prateleira.')->group(function () {
                Route::get('/', PrateleiraLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Prateleira::class);
                Route::get('estante/{id}/create', PrateleiraLivewireCreate::class)->name('create')->can(Policy::Create->value, Prateleira::class);
                Route::get('edit/{id}', PrateleiraLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Prateleira::class);
            });
        });
    });

    Route::prefix('teste')->name('teste.')->group(function () {
        Route::prefix('simulacao')->name('simulacao.')->group(function () {
            Route::get('create', SimulacaoLivewireCreate::class)->name('create')->can(Policy::SimulacaoCreate->value);
            Route::delete('/', [SimulacaoLivewireCreate::class, 'destroy'])->name('destroy')->can(Policy::SimulacaoDelete->value);
        });
    });
});
