<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
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
        $this->registrarAutenticacao();
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
                'samaccountname' => $request->input('username'),
                'password' => $request->input('password'),
            ]);

            return $validado ? Auth::getLastAttempted() : null;
        });
    }
}
