{{--
    View livewire para visualização e edição individual das caixas.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Editar a caixa')">

    <x-trilha-navegacao :model="$this->caixa"/>


    <x-container>

        <div class="space-y-6">

            <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-2">

                <x-form.input
                    wire:key="caixa-ano"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="caixa.ano"
                    wire:target="storeVolume,update"
                    autofocus
                    :editavel="$this->modo_edicao"
                    :erro="$errors->first('caixa.ano')"
                    icone="calendar-range"
                    min="1900"
                    :max="now()->format('Y')"
                    placeholder="aaaa"
                    required
                    :texto="__('Ano')"
                    :title="__('Informe o ano no padrão aaaa')"
                    type="number"/>


                <x-form.input
                    wire:key="caixa-numero"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="caixa.numero"
                    wire:target="caixa.ano,storeVolume,update"
                    :editavel="$this->modo_edicao"
                    :erro="$errors->first('caixa.numero')"
                    icone="tag"
                    min="1"
                    :placeholder="__('Apenas números')"
                    required
                    :texto="__('Número')"
                    :title="__('Informe o número da caixa')"
                    type="number"/>

            </div>


            <x-form.textarea
                wire:key="caixa-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="caixa.descricao"
                wire:target="storeVolume,update"
                :editavel="$this->modo_edicao"
                :erro="$errors->first('caixa.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a caixa')"
                :texto="__('Descrição')"
                :title="__('Descreva a caixa')"
                com_contador/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4">

                <div class="md:col-span-2">

                    <x-form.select
                        wire:key="localidade"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model="localidade_id"
                        wire:target="localidade_id,storeVolume,update"
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


                <div class="md:col-span-2">

                    @if ($this->localidade_id >= 1)

                        <x-form.select
                            wire:key="predios-{{ $this->localidade_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="predio_id"
                            wire:target="predio_id,localidade_id,storeVolume,update"
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


                <div>

                    @if ($this->predio_id >= 1)

                        <x-form.select
                            wire:key="andares-{{ $this->predio_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="andar_id"
                            wire:target="predio_id,andar_id,localidade_id,storeVolume,update"
                            class="w-full"
                            :editavel="$this->modo_edicao"
                            :erro="$errors->first('andar_id')"
                            icone="layers"
                            required
                            :texto="__('Andar')"
                            :title="__('Escolha o andar')">

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


                <div>

                    @if ($this->andar_id >= 1)

                        <x-form.select
                            wire:key="salas-{{ $this->andar_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="sala_id"
                            wire:target="predio_id,andar_id,localidade_id,storeVolume,update"
                            :editavel="$this->modo_edicao"
                            :erro="$errors->first('sala_id')"
                            icone="door-closed"
                            required
                            :texto="__('Sala')"
                            :title="__('Escolha a sala')">

                            <option value="">{{ __('Selecione...') }}</option>

                            @forelse ($this->salas ?? [] as $sala)

                                <option value="{{ $sala->id }}">

                                    {{ $sala->numero }}

                                </option>

                            @empty

                                <option value="-1">{{ __('Nenhum registro encontrado') }}</option>

                            @endforelse

                        </x-form.select>

                    @endif

                </div>


                <div>

                    @if ($this->sala_id >= 1)

                        <x-form.select
                            wire:key="estantes-{{ $this->sala_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="estante_id"
                            wire:target="predio_id,andar_id,sala_id,localidade_id,storeVolume,update"
                            :editavel="$this->modo_edicao"
                            :erro="$errors->first('estante_id')"
                            icone="bookshelf"
                            required
                            :texto="__('Estante')"
                            :title="__('Escolha a estante')">

                            <option value="">{{ __('Selecione...') }}</option>

                            @forelse ($this->estantes ?? [] as $estante)

                                <option value="{{ $estante->id }}">

                                    {{ $estante->para_humano }}

                                </option>

                            @empty

                                <option value="-1">{{ __('Nenhum registro encontrado') }}</option>

                            @endforelse

                        </x-form.select>

                    @endif

                </div>


                <div>

                    @if ($this->estante_id >= 1)

                        <x-form.select
                            wire:key="prateleiras-{{ $this->estante_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="caixa.prateleira_id"
                            wire:target="predio_id,andar_id,sala_id,estante_id,localidade_id,storeVolume,update"
                            :editavel="$this->modo_edicao"
                            :erro="$errors->first('caixa.prateleira_id')"
                            icone="list-nested"
                            required
                            :texto="__('Prateleira')"
                            :title="__('Escolha a prateleira')">

                            <option value="">{{ __('Selecione...') }}</option>

                            @forelse ($this->prateleiras ?? [] as $prateleira)

                                <option value="{{ $prateleira->id }}">

                                    {{ $prateleira->para_humano }}

                                </option>

                            @empty

                                <option value="-1">{{ __('Nenhum registro encontrado') }}</option>

                            @endforelse

                        </x-form.select>

                    @endif

                </div>

            </div>


            @can (\App\Enums\Policy::Update->value, \App\Models\Caixa::class)

                <x-grupo-button>

                    <x-form.button-editar-salvar-cancelar :modo_edicao="$this->modo_edicao"/>

                </x-grupo-button>

            @endcan

        </div>

    </x-container>


    <x-container>

        <x-table.model.volume
            :excluir="$this->excluir"
            :preferencias="$this->preferencias"
            :volumes="$this->volumes"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir
            com_botao_novo/>

    </x-container>

</x-page>
