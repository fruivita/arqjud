{{--
    View livewire para visualização e edição individual da documentação da
    aplicação.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Editar a documentação da rota')">

    <x-container>

        <div class="space-y-6">

            <x-form.input
                wire:key="doc-app-link"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="documentacao.app_link"
                wire:target="update"
                autofocus
                :editavel="$this->modo_edicao"
                :erro="$errors->first('documentacao.app_link')"
                icone="signpost-2"
                maxlength="255"
                placeholder="exemplo.create.index"
                required
                :texto="__('Nome da rota')"
                :title="__('Informe o nome da rota')"
                type="text"
                com_contador/>


            <x-form.input
                wire:key="doc-link"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="documentacao.doc_link"
                wire:target="update"
                :editavel="$this->modo_edicao"
                :erro="$errors->first('documentacao.doc_link')"
                icone="link"
                maxlength="255"
                placeholder="http://example.com/"
                :texto="__('Link da documentação')"
                :title="__('Informe o link para a documentação da rota informada')"
                type="text"
                com_contador/>


                @can (\App\Enums\Policy::Update->value, \App\Models\Documentacao::class)

                    <x-grupo-button>

                        <x-form.button-editar-salvar-cancelar :modo_edicao="$this->modo_edicao"/>

                    </x-grupo-button>

                @endcan

        </div>

    </x-container>

</x-page>
