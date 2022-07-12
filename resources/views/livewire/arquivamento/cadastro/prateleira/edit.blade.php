{{--
    View livewire para visualização e edição individual das prateleiras.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Editar a prateleira')">

    <x-trilha-navegacao :model="$this->prateleira"/>


    <x-container>

        <div class="space-y-6">

            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-form.input
                    wire:key="prateleira-numero"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="prateleira.numero"
                    wire:target="update"
                    autofocus
                    :editavel="$this->modo_edicao"
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
                    wire:target="update"
                    :editavel="$this->modo_edicao"
                    :erro="$errors->first('prateleira.apelido')"
                    icone="symmetry-vertical"
                    maxlength="100"
                    :placeholder="__('Apelido da prateleira')"
                    :texto="__('Apelido')"
                    :title="__('Informe um apelido para a estante')"
                    type="text"
                    com_contador/>

            </div>


            <x-form.textarea
                wire:key="prateleira-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="prateleira.descricao"
                wire:target="update"
                :editavel="$this->modo_edicao"
                :erro="$errors->first('prateleira.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a prateleira')"
                :texto="__('Descrição')"
                :title="__('Descreva a prateleira')"
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


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-3">

                <div>

                    @if ($this->predio_id >= 1)

                        <x-form.select
                            wire:key="andares-{{ $this->predio_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="andar_id"
                            wire:target="predio_id,localidade_id,update"
                            :editavel="$this->modo_edicao"
                            :erro="$errors->first('andar_id')"
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


                <div>

                    @if ($this->andar_id >= 1)

                        <x-form.select
                            wire:key="salas-{{ $this->andar_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="sala_id"
                            wire:target="predio_id,andar_id,localidade_id,update"
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
                            wire:model.defer="prateleira.estante_id"
                            wire:target="predio_id,andar_id,sala_id,localidade_id,update"
                            :editavel="$this->modo_edicao"
                            :erro="$errors->first('prateleira.estante_id')"
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

            </div>


            @can (\App\Enums\Policy::Update->value, \App\Models\Prateleira::class)

                <x-grupo-button>

                    <x-form.button-editar-salvar-cancelar :modo_edicao="$this->modo_edicao"/>

                </x-grupo-button>

            @endcan

        </div>

    </x-container>


    <x-container>

        <x-table.model.caixa
            :caixas="$this->caixas"
            :excluir="$this->excluir"
            :pai="$this->prateleira"
            :preferencias="$this->preferencias"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir
            com_botao_novo/>

    </x-container>

</x-page>
