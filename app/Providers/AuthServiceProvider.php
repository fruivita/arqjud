<?php

namespace App\Providers;

use App\Enums\Policy;
use App\Models\Usuario;
use App\Policies\DelegacaoPolicy;
use App\Policies\ImportacaoPolicy;
use App\Policies\LogPolicy;
use App\Policies\SimulacaoPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\Fortify;

/**
 * @see https://laravel.com/docs/9.x/providers
 * @see https://laravel.com/docs/9.x/authorization
 * @see https://laravel.com/docs/9.x/fortify
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Localidade::class => \App\Policies\LocalidadePolicy::class,
        \App\Models\Predio::class => \App\Policies\PredioPolicy::class,
        \App\Models\Andar::class => \App\Policies\AndarPolicy::class,
        \App\Models\Sala::class => \App\Policies\SalaPolicy::class,
        \App\Models\Estante::class => \App\Policies\EstantePolicy::class,
        \App\Models\Prateleira::class => \App\Policies\PrateleiraPolicy::class,
        \App\Models\Caixa::class => \App\Policies\CaixaPolicy::class,
        \App\Models\VolumeCaixa::class => \App\Policies\VolumeCaixaPolicy::class,
        \App\Models\Configuracao::class => \App\Policies\ConfiguracaoPolicy::class,
        \App\Models\Documentacao::class => \App\Policies\DocumentacaoPolicy::class,
        \App\Models\Perfil::class => \App\Policies\PerfilPolicy::class,
        \App\Models\Permissao::class => \App\Policies\PermissaoPolicy::class,
        \App\Models\Usuario::class => \App\Policies\UsuarioPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registrarGates();
        $this->registrarAutenticacao();
    }

    /**
     * Registra todos os gates de verificação.
     *
     * @return void
     */
    private function registrarGates()
    {
        Gate::before(function (Usuario $usuario) {
            if ($usuario->eSuperAdmin() === true) {
                return true;
            }
        });

        Gate::define(Policy::DelegacaoViewAny->value, [DelegacaoPolicy::class, 'viewAny']);
        Gate::define(Policy::DelegacaoCreate->value, [DelegacaoPolicy::class, 'create']);
        Gate::define(Policy::DelegacaoDelete->value, [DelegacaoPolicy::class, 'delete']);
        Gate::define(Policy::ImportacaoCreate->value, [ImportacaoPolicy::class, 'create']);
        Gate::define(Policy::LogViewAny->value, [LogPolicy::class, 'viewAny']);
        Gate::define(Policy::LogDelete->value, [LogPolicy::class, 'delete']);
        Gate::define(Policy::LogDownload->value, [LogPolicy::class, 'download']);
        Gate::define(Policy::SimulacaoCreate->value, [SimulacaoPolicy::class, 'create']);
        Gate::define(Policy::SimulacaoDelete->value, [SimulacaoPolicy::class, 'delete']);
    }

    /**
     * Registra o método de autenticação.
     *
     * @return void
     */
    private function registrarAutenticacao()
    {
        Fortify::authenticateUsing(function ($request) {
            $validado = Auth::validate([
                'samaccountname' => $request->input('username'),
                'password' => $request->input('password'),
                'fallback' => [
                    'username' => $request->input('username'),
                    'password' => $request->input('password'),
                ],
            ]);

            return $validado ? Auth::getLastAttempted() : null;
        });
    }
}
