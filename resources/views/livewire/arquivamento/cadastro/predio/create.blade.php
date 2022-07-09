{{--
    View livewire para criação dos prédios.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Novos prédios')">

    <x-trilha-navegacao :model="$this->localidade" :root="true"/>


    <x-container>

        <div class="space-y-6">

            <x-chave-valor
                :chave="__('Localidade')"
                :valor="$this->localidade->nome"/>


            <x-form.input
                wire:key="predio-nome"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="predio.nome"
                wire:target="store"
                autofocus
                editavel
                :erro="$errors->first('predio.nome')"
                icone="building"
                maxlength="100"
                :placeholder="__('Nome do prédio')"
                required
                :texto="__('Prédio')"
                :title="__('Informe o nome do prédio')"
                type="text"
                com_contador/>


            <x-form.textarea
                wire:key="predio-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="predio.descricao"
                wire:target="store"
                editavel
                :erro="$errors->first('predio.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre o prédio')"
                :texto="__('Descrição')"
                :title="__('Descreva o prédio')"
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

        <x-table.model.predio
            :predios="$this->predios"
            :excluir="$this->excluir"
            :preferencias="$this->preferencias"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir/>

    </x-container>

</x-page>
