{{--
    View livewire para visualização e edição individual das salas.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Editar a sala')">

    <x-trilha-navegacao :model="$this->sala"/>


    <x-container>

        <div class="space-y-6">

            <x-form.input
                wire:key="sala-numero"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="sala.numero"
                wire:target="update"
                autofocus
                :editavel="$this->modo_edicao"
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
                wire:target="update"
                :editavel="$this->modo_edicao"
                :erro="$errors->first('sala.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a sala')"
                :texto="__('Descrição')"
                :title="__('Descreva a sala')"
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

                    @if ($this->localidade_id >= 1)

                        <x-form.select
                            wire:key="predios-{{ $this->localidade_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="predio_id"
                            wire:target="predio_id,localidade_id,update"
                            :editavel="$this->modo_edicao"
                            :erro="$errors->first('predio_id')"
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


            <div>

                @if ($this->predio_id >= 1)

                    <x-form.select
                        wire:key="andares-{{ $this->predio_id }}"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="sala.andar_id"
                        wire:target="predio_id,localidade_id,update"
                        :editavel="$this->modo_edicao"
                        :erro="$errors->first('sala.andar_id')"
                        icone="layers"
                        required
                        :texto="__('Andar')"
                        :title="__('Escolha a andar')">

                        <option value="">{{ __('Selecione...') }}</option>

                        @forelse ($this->andares ?? [] as $andar)

                            <option value="{{ $andar->id }}">

                                {{ $andar->numero }}

                            </option>

                        @empty

                            <option value="-1">{{ __('Nenhum registro encontrado') }}</option>

                        @endforelse

                    </x-form.select>

                @endif

            </div>


            @can (\App\Enums\Policy::Update->value, \App\Models\Sala::class)

                <x-grupo-button>

                    <x-form.button-editar-salvar-cancelar :modo_edicao="$this->modo_edicao"/>

                </x-grupo-button>

            @endcan

        </div>

    </x-container>


    <x-container>

        <x-table.model.estante
            :excluir="$this->excluir"
            :pai="$this->sala"
            :preferencias="$this->preferencias"
            :estantes="$this->estantes"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir
            com_botao_novo/>

    </x-container>

</x-page>
