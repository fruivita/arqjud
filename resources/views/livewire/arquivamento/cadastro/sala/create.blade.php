{{--
    View livewire para criação dos salas.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Novas salas')">

    <x-trilha-navegacao :model="$this->andar" :root="true"/>


    <x-container>

        <div class="space-y-6">

            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-chave-valor
                    :chave="__('Localidade')"
                    :valor="$this->andar->localidade_nome"/>


                <x-chave-valor
                    :chave="__('Prédio')"
                    :valor="$this->andar->predio_nome"/>

            </div>


            <x-chave-valor
                :chave="__('Andar')"
                :valor="$this->andar->numero"/>


            <x-form.input
                wire:key="sala-numero"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="sala.numero"
                wire:target="store"
                autofocus
                editavel
                :erro="$errors->first('sala.numero')"
                icone="door-closed"
                maxlength="50"
                :placeholder="__('Número da sala')"
                required
                :texto="__('Sala')"
                :title="__('Informe o número da sala')"
                type="text"
                com_contador/>


            <x-form.textarea
                wire:key="sala-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="sala.descricao"
                wire:target="store"
                editavel
                :erro="$errors->first('sala.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a sala')"
                :texto="__('Descrição')"
                :title="__('Descreva a sala')"
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

        <x-table.model.sala
            :excluir="$this->excluir"
            :preferencias="$this->preferencias"
            :salas="$this->salas"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir/>

    </x-container>

</x-page>
