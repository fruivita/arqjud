{{--
    View livewire para visualização e edição individual das configurações da
    aplicação.

    Itens configuráveis:
    - Superadmin: string usuário com permissões full e não delegáveis.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Editar as configurações da aplicação')">

    <x-container>

        <div class="space-y-6">

            <div class="lg:inline-flex">

                <x-form.input
                    wire:key="configuracao-superadmin"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="configuracao.superadmin"
                    wire:target="update"
                    autocomplete="off"
                    autofocus
                    :editavel="$this->modo_edicao"
                    :erro="$errors->first('configuracao.superadmin')"
                    icone="person"
                    maxlength="20"
                    :placeholder="__('Usuário de rede')"
                    required
                    :texto="__('Novas super adminitrador')"
                    :title="__('Informe um usuário de rede')"
                    type="text"
                    com_contador/>

            </div>


            @can (\App\Enums\Policy::Update->value, \App\Models\Configuracao::class)

                <x-grupo-button>

                    <x-form.button-editar-salvar-cancelar :modo_edicao="$this->modo_edicao"/>

                </x-grupo-button>

            @endcan

        </div>

    </x-container>

</x-page>
