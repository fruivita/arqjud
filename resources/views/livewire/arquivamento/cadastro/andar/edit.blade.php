{{--
    View livewire para visualização e edição individual dos andares.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Editar o andar')">

    <x-trilha-navegacao :model="$this->andar"/>


    <x-container>

        <div class="space-y-6">

            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-form.input
                    wire:key="andar-numero"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="andar.numero"
                    wire:target="update"
                    autofocus
                    :editavel="$this->modo_edicao"
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
                    wire:target="update"
                    :editavel="$this->modo_edicao"
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
                wire:target="update"
                :editavel="$this->modo_edicao"
                :erro="$errors->first('andar.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre o andar')"
                :texto="__('Descrição')"
                :title="__('Descreva o andar')"
                com_contador/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <div>

                    <x-form.select
                        wire:key="localidade"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model="localidade_id"
                        wire:target="localidade_id,update"
                        :editavel="$this->modo_edicao"
                        :erro="$errors->first('localidade_id')"
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

                </div>


                <div>

                    @if($this->localidade_id >= 1)

                        <x-form.select
                            wire:key="predios-{{ $this->localidade_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="andar.predio_id"
                            wire:target="localidade_id,update"
                            :editavel="$this->modo_edicao"
                            :erro="$errors->first('andar.predio_id')"
                            icone="building"
                            required
                            :texto="__('Prédio')"
                            :title="__('Escolha o prédio')">

                            <option value="">{{ __('Selecione...') }}</option>

                            @forelse ($this->predios ?? [] as $predio)

                                <option value="{{ $predio->id }}">

                                    {{ $predio->nome }}

                                </option>

                            @empty

                                <option value="-1">{{ __('Nenhum registro encontrado') }}</option>

                            @endforelse

                        </x-form.select>

                    @endif

                </div>

            </div>


            @can(\App\Enums\Policy::Update->value, \App\Models\Andar::class)

                <x-grupo-button>

                    <x-form.button-editar-salvar-cancelar :modo_edicao="$this->modo_edicao"/>

                </x-grupo-button>

            @endcan

        </div>

    </x-container>


    <x-container>

        <x-table.model.sala
            :excluir="$this->excluir"
            :pai="$this->andar"
            :preferencias="$this->preferencias"
            :salas="$this->salas"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir
            com_botao_novo/>

    </x-container>

</x-page>
