<?php

namespace App\Providers;

use App\Enums\Policy;
use App\Policies\ImportacaoPolicy;
use App\Policies\LogPolicy;
use App\Policies\MoverProcessoPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;

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
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
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
        Gate::define(Policy::MoverProcessoCreate->value, [MoverProcessoPolicy::class, Policy::Create->value]);
        Gate::define(Policy::ImportacaoCreate->value, [ImportacaoPolicy::class, Policy::Create->value]);
        Gate::define(Policy::LogViewAny->value, [LogPolicy::class, Policy::ViewAny->value]);
        Gate::define(Policy::LogView->value, [LogPolicy::class, Policy::View->value]);
        Gate::define(Policy::LogDelete->value, [LogPolicy::class, Policy::Delete->value]);
    }

    /**
     * Registra o método de autenticação.
     *
     * @return void
     */
    private function registrarAutenticacao()
    {
        Fortify::authenticateUsing(function (LoginRequest $request) {
            $validado = Auth::validate([
                'matricula' => $request->input('matricula'),
                'password' => $request->input('password'),
            ]);

            return $validado ? Auth::getLastAttempted() : null;
        });
    }
}
