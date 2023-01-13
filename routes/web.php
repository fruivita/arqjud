<?php

use App\Enums\Policy;
use App\Http\Controllers\Administracao\ImportacaoController;
use App\Http\Controllers\Administracao\LogController;
use App\Http\Controllers\Administracao\LotacaoController;
use App\Http\Controllers\Administracao\PerfilController;
use App\Http\Controllers\Administracao\PermissaoController;
use App\Http\Controllers\Atendimento\ReceberProcessoController;
use App\Http\Controllers\Atendimento\EntregarProcessoController;
use App\Http\Controllers\Atendimento\GuiaController;
use App\Http\Controllers\Atendimento\SolicitacaoController;
use App\Http\Controllers\Autorizacao\UsuarioController;
use App\Http\Controllers\Cadastro\Andar\AndarController;
use App\Http\Controllers\Cadastro\Caixa\CaixaController;
use App\Http\Controllers\Cadastro\Estante\EstanteController;
use App\Http\Controllers\Cadastro\Localidade\LocalidadeController;
use App\Http\Controllers\Cadastro\Prateleira\PrateleiraController;
use App\Http\Controllers\Cadastro\Predio\PredioController;
use App\Http\Controllers\Cadastro\Processo\ProcessoController;
use App\Http\Controllers\Cadastro\Sala\SalaController;
use App\Http\Controllers\Cadastro\VolumeCaixa\VolumeCaixaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Movimentacao\MoveProcessoEntreCaixaController;
use App\Http\Controllers\Solicitacao\SolicitacaoController as SolicitacaoExternaController;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Guia;
use App\Models\Localidade;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Processo;
use App\Models\Sala;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Models\VolumeCaixa;
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
        Route::post('/', [HomeController::class, 'show'])->can(Policy::View->value, Processo::class);
    });

    Route::prefix('atendimento')->name('atendimento.')->group(function () {
        Route::prefix('solicitar-processo')->name('solicitar-processo.')->group(function () {
            Route::get('/', [SolicitacaoController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Solicitacao::class);
            Route::get('create', [SolicitacaoController::class, 'create'])->name('create')->can(Policy::Create->value, Solicitacao::class);
            Route::post('/', [SolicitacaoController::class, 'store'])->name('store')->can(Policy::Create->value, Solicitacao::class);
            Route::delete('{solicitacao}', [SolicitacaoController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'solicitacao');
        });

        Route::prefix('entregar-processo')->name('entregar-processo.')->group(function () {
            Route::get('create', [EntregarProcessoController::class, 'create'])->name('create')->can(Policy::Update->value, Solicitacao::class);
            Route::post('/', [EntregarProcessoController::class, 'store'])->name('store')->can(Policy::Update->value, Solicitacao::class);
        });

        Route::prefix('receber-processo')->name('receber-processo.')->group(function () {
            Route::get('create', [ReceberProcessoController::class, 'create'])->name('create')->can(Policy::Update->value, Solicitacao::class);
            Route::post('/', [ReceberProcessoController::class, 'store'])->name('store')->can(Policy::Update->value, Solicitacao::class);
        });

        Route::prefix('guia')->name('guia.')->group(function () {
            Route::get('/', [GuiaController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Guia::class);
            Route::get('{guia}', [GuiaController::class, 'show'])->name('show')->can(Policy::View->value, Guia::class);
            Route::get('pdf/{guia}', [GuiaController::class, 'pdf'])->name('pdf')->can(Policy::View->value, Guia::class);
        });
    });

    Route::prefix('solicitacao')->name('solicitacao.')->group(function () {
        Route::get('/', [SolicitacaoExternaController::class, 'index'])->name('index')->can(Policy::ExternoViewAny->value, Solicitacao::class);
        Route::get('create', [SolicitacaoExternaController::class, 'create'])->name('create')->can(Policy::ExternoCreate->value, Solicitacao::class);
        Route::post('/', [SolicitacaoExternaController::class, 'store'])->name('store')->can(Policy::ExternoCreate->value, Solicitacao::class);
        Route::delete('{solicitacao}', [SolicitacaoExternaController::class, 'destroy'])->name('destroy')->can(Policy::ExternoDelete->value, 'solicitacao');
    });

    Route::prefix('movimentacao')->name('movimentacao.')->group(function () {
        Route::prefix('entre-caixas')->name('entre-caixas.')->group(function () {
            Route::get('create', [MoveProcessoEntreCaixaController::class, 'create'])->name('create')->can(Policy::MoverProcessoCreate->value);
            Route::post('/', [MoveProcessoEntreCaixaController::class, 'store'])->name('store')->can(Policy::MoverProcessoCreate->value);
        });
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

        Route::prefix('predio')->name('predio.')->group(function () {
            Route::get('/', [PredioController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Predio::class);
            Route::get('localidade/{localidade}/create', [PredioController::class, 'create'])->name('create')->can(Policy::Create->value, Predio::class);
            Route::post('localidade/{localidade}', [PredioController::class, 'store'])->name('store')->can(Policy::Create->value, Predio::class);
            Route::get('{predio}/edit', [PredioController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Predio::class);
            Route::patch('{predio}', [PredioController::class, 'update'])->name('update')->can(Policy::Update->value, Predio::class);
            Route::delete('{predio}', [PredioController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'predio');
        });

        Route::prefix('andar')->name('andar.')->group(function () {
            Route::get('/', [AndarController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Andar::class);
            Route::get('predio/{predio}/create', [AndarController::class, 'create'])->name('create')->can(Policy::Create->value, Andar::class);
            Route::post('predio/{predio}', [AndarController::class, 'store'])->name('store')->can(Policy::Create->value, Andar::class);
            Route::get('{andar}/edit', [AndarController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Andar::class);
            Route::patch('{andar}', [AndarController::class, 'update'])->name('update')->can(Policy::Update->value, Andar::class);
            Route::delete('{andar}', [AndarController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'andar');
        });

        Route::prefix('sala')->name('sala.')->group(function () {
            Route::get('/', [SalaController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Sala::class);
            Route::get('andar/{andar}/create', [SalaController::class, 'create'])->name('create')->can(Policy::Create->value, Sala::class);
            Route::post('andar/{andar}', [SalaController::class, 'store'])->name('store')->can(Policy::Create->value, Sala::class);
            Route::get('{sala}/edit', [SalaController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Sala::class);
            Route::patch('{sala}', [SalaController::class, 'update'])->name('update')->can(Policy::Update->value, Sala::class);
            Route::delete('{sala}', [SalaController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'sala');
        });

        Route::prefix('estante')->name('estante.')->group(function () {
            Route::get('/', [EstanteController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Estante::class);
            Route::get('sala/{sala}/create', [EstanteController::class, 'create'])->name('create')->can(Policy::Create->value, Estante::class);
            Route::post('sala/{sala}', [EstanteController::class, 'store'])->name('store')->can(Policy::Create->value, Estante::class);
            Route::get('{estante}/edit', [EstanteController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Estante::class);
            Route::patch('{estante}', [EstanteController::class, 'update'])->name('update')->can(Policy::Update->value, Estante::class);
            Route::delete('{estante}', [EstanteController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'estante');
        });

        Route::prefix('prateleira')->name('prateleira.')->group(function () {
            Route::get('/', [PrateleiraController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Prateleira::class);
            Route::get('estante/{estante}/create', [PrateleiraController::class, 'create'])->name('create')->can(Policy::Create->value, Prateleira::class);
            Route::post('estante/{estante}', [PrateleiraController::class, 'store'])->name('store')->can(Policy::Create->value, Prateleira::class);
            Route::get('{prateleira}/edit', [PrateleiraController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Prateleira::class);
            Route::patch('{prateleira}', [PrateleiraController::class, 'update'])->name('update')->can(Policy::Update->value, Prateleira::class);
            Route::delete('{prateleira}', [PrateleiraController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'prateleira');
        });

        Route::prefix('caixa')->name('caixa.')->group(function () {
            Route::get('/', [CaixaController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Caixa::class);
            Route::get('prateleira/{prateleira}/create', [CaixaController::class, 'create'])->name('create')->can(Policy::Create->value, Caixa::class);
            Route::post('prateleira/{prateleira}', [CaixaController::class, 'store'])->name('store')->can(Policy::Create->value, Caixa::class);
            Route::get('{caixa}/edit', [CaixaController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Caixa::class);
            Route::patch('{caixa}', [CaixaController::class, 'update'])->name('update')->can(Policy::Update->value, Caixa::class);
            Route::delete('{caixa}', [CaixaController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'caixa');
        });

        Route::prefix('volume-caixa')->name('volume-caixa.')->group(function () {
            Route::get('/', [VolumeCaixaController::class, 'index'])->name('index')->can(Policy::ViewAny->value, VolumeCaixa::class);
            Route::get('caixa/{caixa}/create', [VolumeCaixaController::class, 'create'])->name('create')->can(Policy::Create->value, VolumeCaixa::class);
            Route::post('caixa/{caixa}', [VolumeCaixaController::class, 'store'])->name('store')->can(Policy::Create->value, VolumeCaixa::class);
            Route::get('{volume_caixa}/edit', [VolumeCaixaController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, VolumeCaixa::class);
            Route::patch('{volume_caixa}', [VolumeCaixaController::class, 'update'])->name('update')->can(Policy::Update->value, VolumeCaixa::class);
            Route::delete('{volume_caixa}', [VolumeCaixaController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'volume_caixa');
        });

        Route::prefix('processo')->name('processo.')->group(function () {
            Route::get('/', [ProcessoController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Processo::class);
            Route::get('volume-caixa/{volume_caixa}/create', [ProcessoController::class, 'create'])->name('create')->can(Policy::Create->value, Processo::class);
            Route::post('volume-caixa/{volume_caixa}', [ProcessoController::class, 'store'])->name('store')->can(Policy::Create->value, Processo::class);
            Route::get('{processo}/edit', [ProcessoController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Processo::class);
            Route::patch('{processo}', [ProcessoController::class, 'update'])->name('update')->can(Policy::Update->value, Processo::class);
            Route::delete('{processo}', [ProcessoController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'processo');
        });
    });

    Route::prefix('autorizacao')->name('autorizacao.')->group(function () {
        Route::prefix('usuario')->name('usuario.')->group(function () {
            Route::get('/', [UsuarioController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Usuario::class);
            Route::get('{usuario}/edit', [UsuarioController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, 'usuario');
            Route::patch('{usuario}', [UsuarioController::class, 'update'])->name('update')->can(Policy::Update->value, 'usuario');
        });
    });

    Route::prefix('administracao')->name('administracao.')->group(function () {
        Route::prefix('perfil')->name('perfil.')->group(function () {
            Route::get('/', [PerfilController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Perfil::class);
            Route::get('create', [PerfilController::class, 'create'])->name('create')->can(Policy::Create->value, Perfil::class);
            Route::post('/', [PerfilController::class, 'store'])->name('store')->can(Policy::Create->value, Perfil::class);
            Route::get('{perfil}/edit', [PerfilController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Perfil::class);
            Route::patch('{perfil}', [PerfilController::class, 'update'])->name('update')->can(Policy::Update->value, Perfil::class);
            Route::delete('{perfil}', [PerfilController::class, 'destroy'])->name('destroy')->can(Policy::Delete->value, 'perfil');
        });

        Route::prefix('permissao')->name('permissao.')->group(function () {
            Route::get('/', [PermissaoController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Permissao::class);
            Route::get('create', [PermissaoController::class, 'create'])->name('create')->can(Policy::Create->value, Permissao::class);
            Route::post('/', [PermissaoController::class, 'store'])->name('store')->can(Policy::Create->value, Permissao::class);
            Route::get('{permissao}/edit', [PermissaoController::class, 'edit'])->name('edit')->can(Policy::ViewOrUpdate->value, Permissao::class);
            Route::patch('{permissao}', [PermissaoController::class, 'update'])->name('update')->can(Policy::Update->value, Permissao::class);
        });

        Route::prefix('lotacao')->name('lotacao.')->group(function () {
            Route::get('/', [LotacaoController::class, 'index'])->name('index')->can(Policy::ViewAny->value, Lotacao::class);
            Route::patch('{lotacao}', [LotacaoController::class, 'update'])->name('update')->can(Policy::Update->value, Lotacao::class);
        });

        Route::prefix('importacao')->name('importacao.')->group(function () {
            Route::get('create', [ImportacaoController::class, 'create'])->name('create')->can(Policy::ImportacaoCreate->value);
            Route::post('/', [ImportacaoController::class, 'store'])->name('store')->can(Policy::ImportacaoCreate->value);
        });

        Route::prefix('log')->name('log.')->group(function () {
            Route::get('/', [LogController::class, 'index'])->name('index')->can(Policy::LogViewAny->value);
            Route::get('{log}/edit', [LogController::class, 'show'])->name('show')->can(Policy::LogView->value);
            Route::get('{log}/download', [LogController::class, 'download'])->name('download')->can(Policy::LogView->value);
            Route::delete('{log}', [LogController::class, 'destroy'])->name('destroy')->can(Policy::LogDelete->value, 'log');
        });
    });
});
