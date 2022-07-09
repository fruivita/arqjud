{{--
    View livewire para criação da documentação da aplicação.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Novas documentações de rotas')">

    <x-container>

        <div class="space-y-6">

            <x-form.input
            wire:key="doc-app-link"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="documentacao.app_link"
                wire:target="store"
                autofocus
                editavel
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
                wire:target="store"
                editavel
                :erro="$errors->first('documentacao.doc_link')"
                icone="link"
                maxlength="255"
                placeholder="http://example.com/"
                :texto="__('Link da documentação')"
                :title="__('Informe o link para a documentação da rota informada')"
                type="text"
                com_contador/>


            <x-grupo-button>

                <x-feedback.inline/>


                <x-button
                    wire:click="store"
                    wire:key="btn-store"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    class="btn-acao"
                    icone="save"
                    :texto="__('Salvar')"
                    :title="__('Salvar o registro')"
                    type="button"/>

            </x-grupo-button>

        </div>

    </x-container>


    <x-container>

        <x-table.model.documentacao
            :excluir="$this->excluir"
            :documentacoes="$this->documentacoes"
            :preferencias="$this->preferencias"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir/>

    </x-container>

</x-page>
