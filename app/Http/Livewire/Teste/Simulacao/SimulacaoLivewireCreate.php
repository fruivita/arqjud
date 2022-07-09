<?php

namespace App\Http\Livewire\Teste\Simulacao;

use App\Enums\Policy;
use App\Rules\NaoUsuarioAutenticado;
use App\Rules\UsuarioLdap;
use App\Traits\ComUsuarioLdapImportavel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class SimulacaoLivewireCreate extends Component
{
    use AuthorizesRequests;
    use ComUsuarioLdapImportavel;

    /**
     * Usuário do servidor LDAP que será importado.
     *
     * @var string
     */
    public $username;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'username' => [
                'bail',
                'required',
                'string',
                'max:20',
                new NaoUsuarioAutenticado(),
                new UsuarioLdap(),
            ],
        ];
    }

    /**
     * Atributos customizados para os erros de validação.
     *
     * @return array<string, mixed>
     */
    protected function validationAttributes()
    {
        return [
            'username' => __('Usuário de rede'),
        ];
    }

    /**
     * Executado em cada request, imediatamente após o componente ser
     * instanciado, mas antes de qualquer outro método do ciclo de vida ser
     * acionado.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::SimulacaoCreate->value);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.teste.simulacao.create')->layout('layouts.app');
    }

    /**
     * Substitui o usuário autenticado para simular o uso da aplicação por
     * por outro usuário.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $this->validate();

        session()->put([
            'simulado' => $this->importarUsuarioLdap($this->username),
            'simulador' => Auth::user(),
        ]);

        return redirect()->route('home');
    }

    /**
     * Desfaz a simulação, retornando o usuároi autenticado ao que iniciou a simulação.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        $this->authorize(Policy::SimulacaoDelete->value);

        Auth::login(session()->pull('simulador'));

        session()->forget(['simulado']);

        return back();
    }
}
