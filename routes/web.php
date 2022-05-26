<?php

use App\Enums\Policy;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Administration\Configuration\ConfigurationLivewireShow;
use App\Http\Livewire\Administration\Configuration\ConfigurationLivewireUpdate;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireCreate;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireIndex;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireUpdate;
use App\Http\Livewire\Administration\Importation\ImportationLivewireCreate;
use App\Http\Livewire\Administration\Log\LogLivewireIndex;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireCreate;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireIndex;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireShow;
use App\Http\Livewire\Authorization\Delegation\DelegationLivewireIndex;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireIndex;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireShow;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireUpdate;
use App\Http\Livewire\Authorization\Role\RoleLivewireIndex;
use App\Http\Livewire\Authorization\Role\RoleLivewireShow;
use App\Http\Livewire\Authorization\Role\RoleLivewireUpdate;
use App\Http\Livewire\Authorization\User\UserLivewireIndex;
use App\Http\Livewire\Test\Simulation\SimulationLivewireCreate;
use App\Models\Box;
use App\Models\Configuration;
use App\Models\Documentation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
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

    Route::prefix('autorizacao')->name('authorization.')->group(function () {
        Route::prefix('delegacao')->name('delegations.')->group(function () {
            Route::get('/', DelegationLivewireIndex::class)->name('index')->can(Policy::DelegationViewAny->value);
        });

        Route::prefix('perfil')->name('role.')->group(function () {
            Route::get('/', RoleLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Role::class);
            Route::get('show/{role}', RoleLivewireShow::class)->name('show')->can(Policy::View->value, Role::class);
            Route::get('edit/{role}', RoleLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Role::class);
        });

        Route::prefix('permissao')->name('permission.')->group(function () {
            Route::get('/', PermissionLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Permission::class);
            Route::get('show/{permission}', PermissionLivewireShow::class)->name('show')->can(Policy::View->value, Permission::class);
            Route::get('edit/{permission}', PermissionLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Permission::class);
        });

        Route::prefix('usuario')->name('user.')->group(function () {
            Route::get('/', UserLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, User::class);
        });
    });

    Route::prefix('administracao')->name('administration.')->group(function () {
        Route::prefix('configuracao')->name('configuration.')->group(function () {
            Route::get('show', ConfigurationLivewireShow::class)->name('show')->can(Policy::View->value, Configuration::class);
            Route::get('edit', ConfigurationLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Configuration::class);
        });

        Route::prefix('doc')->name('doc.')->group(function () {
            Route::get('/', DocumentationLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Documentation::class);
            Route::get('create', DocumentationLivewireCreate::class)->name('create')->can(Policy::Create->value, Documentation::class);
            Route::get('edit/{doc}', DocumentationLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Documentation::class);
        });

        Route::prefix('importacao')->name('importation.')->group(function () {
            Route::get('create', ImportationLivewireCreate::class)->name('create')->can(Policy::ImportationCreate->value);
        });

        Route::prefix('log')->name('log.')->group(function () {
            Route::get('/', LogLivewireIndex::class)->name('index')->can(Policy::LogViewAny->value);
        });
    });

    Route::prefix('arquivamento')->name('archiving.')->group(function () {
        Route::prefix('cadastro')->name('register.')->group(function () {
            Route::prefix('caixa')->name('box.')->group(function () {
                Route::get('/', BoxLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Box::class);
                Route::get('show/{box}', BoxLivewireShow::class)->name('show')->can(Policy::View->value, Box::class);
                Route::get('create', BoxLivewireCreate::class)->name('create')->can(Policy::Create->value, Box::class);
            });
        });
    });

    Route::prefix('teste')->name('test.')->group(function () {
        Route::prefix('simulacao')->name('simulation.')->group(function () {
            Route::get('create', SimulationLivewireCreate::class)->name('create')->can(Policy::SimulationCreate->value);
            Route::delete('/', [SimulationLivewireCreate::class, 'destroy'])->name('destroy')->can(Policy::SimulationDelete->value);
        });
    });
});
