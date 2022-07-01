<?php

use App\Enums\Policy;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Administration\Configuration\ConfigurationLivewireUpdate;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireCreate;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireIndex;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireUpdate;
use App\Http\Livewire\Administration\Importation\ImportationLivewireCreate;
use App\Http\Livewire\Administration\Log\LogLivewireIndex;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireCreate;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireIndex;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireUpdate;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireCreate;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireIndex;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireUpdate;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireCreate;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireIndex;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireUpdate;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireCreate;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireIndex;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireUpdate;
use App\Http\Livewire\Archiving\Register\Shelf\ShelfLivewireCreate;
use App\Http\Livewire\Archiving\Register\Shelf\ShelfLivewireIndex;
use App\Http\Livewire\Archiving\Register\Shelf\ShelfLivewireUpdate;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireCreate;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireIndex;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireUpdate;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireCreate;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireIndex;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireUpdate;
use App\Http\Livewire\Authorization\Delegation\DelegationLivewireIndex;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireIndex;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireUpdate;
use App\Http\Livewire\Authorization\Role\RoleLivewireIndex;
use App\Http\Livewire\Authorization\Role\RoleLivewireUpdate;
use App\Http\Livewire\Authorization\User\UserLivewireIndex;
use App\Http\Livewire\Test\Simulation\SimulationLivewireCreate;
use App\Models\Box;
use App\Models\Building;
use App\Models\Configuration;
use App\Models\Documentation;
use App\Models\Floor;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Site;
use App\Models\Stand;
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
            Route::get('edit/{role}', RoleLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Role::class);
        });

        Route::prefix('permissao')->name('permission.')->group(function () {
            Route::get('/', PermissionLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Permission::class);
            Route::get('edit/{permission}', PermissionLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Permission::class);
        });

        Route::prefix('usuario')->name('user.')->group(function () {
            Route::get('/', UserLivewireIndex::class)->name('index')->can(Policy::ViewAnyOrUpdate->value, User::class);
        });
    });

    Route::prefix('administracao')->name('administration.')->group(function () {
        Route::prefix('configuracao')->name('configuration.')->group(function () {
            Route::get('edit', ConfigurationLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Configuration::class);
        });

        Route::prefix('doc')->name('doc.')->group(function () {
            Route::get('/', DocumentationLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Documentation::class);
            Route::get('create', DocumentationLivewireCreate::class)->name('create')->can(Policy::Create->value, Documentation::class);
            Route::get('edit/{doc}', DocumentationLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Documentation::class);
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
                Route::get('prateleira/{id}/create', BoxLivewireCreate::class)->name('create')->can(Policy::Create->value, Box::class);
                Route::get('edit/{id}', BoxLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Box::class);
            });

            Route::prefix('localidade')->name('site.')->group(function () {
                Route::get('/', SiteLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Site::class);
                Route::get('create', SiteLivewireCreate::class)->name('create')->can(Policy::Create->value, Site::class);
                Route::get('edit/{site}', SiteLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Site::class);
            });

            Route::prefix('predio')->name('building.')->group(function () {
                Route::get('/', BuildingLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Building::class);
                Route::get('localidade/{id}/create', BuildingLivewireCreate::class)->name('create')->can(Policy::Create->value, Building::class);
                Route::get('edit/{id}', BuildingLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Building::class);
            });

            Route::prefix('andar')->name('floor.')->group(function () {
                Route::get('/', FloorLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Floor::class);
                Route::get('predio/{id}/create', FloorLivewireCreate::class)->name('create')->can(Policy::Create->value, Floor::class);
                Route::get('edit/{id}', FloorLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Floor::class);
            });

            Route::prefix('sala')->name('room.')->group(function () {
                Route::get('/', RoomLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Room::class);
                Route::get('andar/{id}/create', RoomLivewireCreate::class)->name('create')->can(Policy::Create->value, Room::class);
                Route::get('edit/{id}', RoomLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Room::class);
            });

            Route::prefix('estante')->name('stand.')->group(function () {
                Route::get('/', StandLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Stand::class);
                Route::get('sala/{id}/create', StandLivewireCreate::class)->name('create')->can(Policy::Create->value, Stand::class);
                Route::get('edit/{id}', StandLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Stand::class);
            });

            Route::prefix('prateleira')->name('shelf.')->group(function () {
                Route::get('/', ShelfLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Shelf::class);
                Route::get('estante/{id}/create', ShelfLivewireCreate::class)->name('create')->can(Policy::Create->value, Shelf::class);
                Route::get('edit/{id}', ShelfLivewireUpdate::class)->name('edit')->can(Policy::ViewOrUpdate->value, Shelf::class);
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
