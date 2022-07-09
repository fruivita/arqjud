{{--
    View livewire para criação dos andares.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Novas andares')">

    <x-trilha-navegacao :model="$this->predio" :root="true"/>


    <x-container>

        <div class="space-y-6">

            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-chave-valor
                    :chave="__('Localidade')"
                    :valor="$this->predio->localidade_nome"/>


                <x-chave-valor
                    :chave="__('Prédio')"
                    :valor="$this->predio->nome"/>


                <x-form.input
                    wire:key="andar-numero"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="andar.numero"
                    wire:target="store"
                    autofocus
                    editavel
                    :erro="$errors->first('andar.numero')"
                    icone="layers"
                    min="-100"
                    max="300"
                    :placeholder="__('Apenas números')"
                    required
                    :texto="__('Andar')"
                    :title="__('Informe o número do andar')"
                    type="number"/>


                <x-form.input
                    wire:key="andar-apelido"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="andar.apelido"
                    wire:target="store"
                    editavel
                    :erro="$errors->first('andar.apelido')"
                    icone="symmetry-vertical"
                    maxlength="100"
                    :placeholder="__('Sugestão: Garagem, G1, Térreo, 10º...')"
                    :texto="__('Apelido')"
                    :title="__('Informe um apelido para o andar')"
                    type="text"
                    com_contador/>

            </div>


            <x-form.textarea
                wire:key="andar-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="andar.descricao"
                wire:target="store"
                editavel
                :erro="$errors->first('andar.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre o andar')"
                :texto="__('Descrição')"
                :title="__('Descreva o andar')"
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

        <x-table.model.andar
            :excluir="$this->excluir"
            :andares="$this->andares"
            :preferencias="$this->preferencias"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir/>

    </x-container>

</x-page>
