{{--
    View livewire para criação das prateleiras.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Novas prateleiras')">

    <x-trilha-navegacao :model="$this->estante" :root="true"/>


    <x-container>

        <div class="space-y-6">

            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-chave-valor
                    :chave="__('Localidade')"
                    :valor="$this->estante->localidade_nome"/>


                <x-chave-valor
                    :chave="__('Prédio')"
                    :valor="$this->estante->predio_nome"/>

            </div>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-3">

                <x-chave-valor
                    :chave="__('Andar')"
                    :valor="$this->estante->andar_numero"/>


                <x-chave-valor
                    :chave="__('Sala')"
                    :valor="$this->estante->sala_numero"/>


                <x-chave-valor
                    :chave="__('Estante')"
                    :valor="$this->estante->para_humano"/>

            </div>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-form.input
                    wire:key="prateleira-numero"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="prateleira.numero"
                    wire:target="store"
                    autofocus
                    editavel
                    :erro="$errors->first('prateleira.numero')"
                    icone="list-nested"
                    min="1"
                    max="100000"
                    :placeholder="__('Apenas números')"
                    required
                    :texto="__('Prateleira')"
                    :title="__('Informe o número da prateleira')"
                    type="number"/>


                <x-form.input
                    wire:key="prateleira-apelido"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="prateleira.apelido"
                    wire:target="store"
                    editavel
                    :erro="$errors->first('prateleira.apelido')"
                    icone="symmetry-vertical"
                    maxlength="100"
                    :placeholder="__('Apelido da prateleira')"
                    :texto="__('Apelido')"
                    :title="__('Informe um apelido para a prateleira')"
                    type="text"
                    com_contador/>

            </div>


            <x-form.textarea
                wire:key="prateleira-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="prateleira.descricao"
                wire:target="store"
                editavel
                :erro="$errors->first('prateleira.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a prateleira')"
                :texto="__('Descrição')"
                :title="__('Descreva a prateleira')"
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

        <x-table.model.prateleira
            :excluir="$this->excluir"
            :preferencias="$this->preferencias"
            :prateleiras="$this->prateleiras"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir/>

    </x-container>

</x-page>
