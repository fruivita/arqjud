<?php

namespace App\Http\Livewire\Administracao\Configuracao;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComFeedback;
use App\Models\Configuracao;
use App\Rules\UsuarioLdap;
use App\Traits\ComUsuarioLdapImportavel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ConfiguracaoLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use ComFeedback;
    use ComUsuarioLdapImportavel;

    /**
     * Se o componente deve ser renderizado no modo edição.
     *
     * @var bool
     */
    public bool $modo_edicao = false;

    /**
     * Item em edição.
     *
     * @var \App\Models\Configuracao
     */
    public Configuracao $configuracao;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'configuracao.superadmin' => [
                'bail',
                'required',
                'string',
                'max:20',
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
            'configuracao.superadmin' => __('Super administrador'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Configuracao::class);
    }

    /**
     * Executado uma única vez, imediatamente após o componente ser
     * instanciado, mas antes do método render() ser acionado. É acionado
     * apenas no carregamento inicial da página e nunca mais chamado, inclusive
     * nas atualizações do componente.
     *
     * @return void
     */
    public function mount()
    {
        $this->configuracao = Configuracao::findOrFail(Configuracao::ID);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administracao.configuracao.edit')->layout('layouts.app');
    }

    /**
     * Substitui o usuário autenticado pelo usuário informado para simular o
     * uso da aplicação.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Configuracao::class);

        $this->validate();

        $this->importarUsuarioLdap($this->configuracao->superadmin);

        $salvo = $this->configuracao->save();

        $this->reset('modo_edicao');

        $this->flashSelf($salvo);
    }
}
