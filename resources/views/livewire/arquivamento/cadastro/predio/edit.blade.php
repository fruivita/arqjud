{{--
    View livewire para visualização e edição individual dos prédios.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Editar o prédio')">

    <x-trilha-navegacao :model="$this->predio"/>


    <x-container>

        <div class="space-y-6">

            <x-form.input
                wire:key="predio-nome"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="predio.nome"
                wire:target="update"
                autofocus
                :editavel="$this->modo_edicao"
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
                wire:target="update"
                :editavel="$this->modo_edicao"
                :erro="$errors->first('predio.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre o prédio')"
                :texto="__('Descrição')"
                :title="__('Descreva o prédio')"
                com_contador/>


            <x-form.select
                wire:key="predio-localidade"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="predio.localidade_id"
                wire:target="update"
                :editavel="$this->modo_edicao"
                :erro="$errors->first('predio.localidade_id')"
                icone="pin-map"
                required
                :texto="__('Localidade')"
                :title="__('Escolha a localidade')">

                <option value="">{{ __('Selecione...') }}</option>


                @forelse ($this->localidades ?? [] as $localidade)

                    <option value="{{ $localidade->id }}">

                        {{ $localidade->nome }}

                    </option>

                @empty

                    <option value="-1">{{ __('Nenhum registro encontrado') }}</option>

                @endforelse

            </x-form.select>


            @can(\App\Enums\Policy::Update->value, \App\Models\Predio::class)

                <x-grupo-button>

                    <x-form.button-editar-salvar-cancelar :modo_edicao="$this->modo_edicao"/>

                </x-grupo-button>

            @endcan

        </div>

    </x-container>


    <x-container>

        <x-table.model.andar
            :excluir="$this->excluir"
            :andares="$this->andares"
            :pai="$this->predio"
            :preferencias="$this->preferencias"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir
            com_botao_novo/>

    </x-container>

</x-page>
