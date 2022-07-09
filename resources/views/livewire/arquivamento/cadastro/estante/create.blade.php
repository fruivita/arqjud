{{--
    View livewire para criação dos estantes.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Novas estantes')">

    <x-trilha-navegacao :model="$this->sala" :root="true"/>


    <x-container>

        <div class="space-y-6">

            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-chave-valor
                    :chave="__('Localidade')"
                    :valor="$this->sala->localidade_nome"/>


                <x-chave-valor
                    :chave="__('Prédio')"
                    :valor="$this->sala->predio_nome"/>


                <x-chave-valor
                    :chave="__('Andar')"
                    :valor="$this->sala->andar_numero"/>


                <x-chave-valor
                    :chave="__('Sala')"
                    :valor="$this->sala->numero"/>


                <x-form.input
                    wire:key="estante-numero"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="estante.numero"
                    wire:target="store"
                    autofocus
                    editavel
                    :erro="$errors->first('estante.numero')"
                    icone="bookshelf"
                    min="1"
                    max="100000"
                    :placeholder="__('Apenas números')"
                    required
                    :texto="__('Estante')"
                    :title="__('Informe o número da estante')"
                    type="number"/>


                <x-form.input
                    wire:key="estante-apelido"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="estante.apelido"
                    wire:target="store"
                    editavel
                    :erro="$errors->first('estante.apelido')"
                    icone="symmetry-vertical"
                    maxlength="100"
                    :placeholder="__('Apelido da estante')"
                    :texto="__('Apelido')"
                    :title="__('Informe um apelido para a estante')"
                    type="text"
                    com_contador/>

            </div>


            <x-form.textarea
                wire:key="estante-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="estante.descricao"
                wire:target="store"
                editavel
                :erro="$errors->first('estante.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a estante')"
                :texto="__('Descrição')"
                :title="__('Descreva a estante')"
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

        <x-table.model.estante
            :excluir="$this->excluir"
            :preferencias="$this->preferencias"
            :estantes="$this->estantes"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir/>

    </x-container>

</x-page>
