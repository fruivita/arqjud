{{--
    View livewire para criação das caixas.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Novas caixas')">

    <x-trilha-navegacao :model="$this->prateleira" :root="true"/>


    <x-container>

        <div class="space-y-6">

            <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4">

                <x-chave-valor
                    class="md:col-span-2"
                    :chave="__('Localidade')"
                    :valor="$this->prateleira->localidade_nome"/>


                <x-chave-valor
                    class="md:col-span-2"
                    :chave="__('Prédio')"
                    :valor="$this->prateleira->predio_nome"/>


                <x-chave-valor
                    :chave="__('Andar')"
                    :valor="$this->prateleira->andar_numero"/>


                <x-chave-valor
                    :chave="__('Sala')"
                    :valor="$this->prateleira->sala_numero"/>


                <x-chave-valor
                    :chave="__('Estante')"
                    :valor="$this->prateleira->estante_para_humano"/>


                <x-chave-valor
                    :chave="__('Prateleira')"
                    :valor="$this->prateleira->para_humano"/>


                <div class="md:col-span-2">

                    <x-form.select
                        wire:key="localidades-criadoras"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="caixa.localidade_criadora_id"
                        wire:target="sugerirNumeroCaixa,store"
                        editavel
                        :erro="$errors->first('caixa.localidade_criadora_id')"
                        icone="pin-map"
                        required
                        :texto="__('Localidade criadora')"
                        :title="__('Escolha a localidade')">

                        <option value="">{{ __('Selecione...') }}</option>

                        @forelse ($this->localidades_criadoras ?? [] as $localidade)

                            <option value="{{ $localidade->id }}">

                                {{ $localidade->nome }}

                            </option>

                        @empty

                            <option value="-1">{{ __('Nenhum registro encontrado') }}</option>

                        @endforelse

                    </x-form.select>

                </div>


                <x-form.checkbox-com-titulo
                    wire:key="caixa-guarda-permanente"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="caixa.guarda_permanente"
                    wire:target="sugerirNumeroCaixa,store"
                    editavel
                    :erro="$errors->first('caixa.guarda_permanente')"
                    name="guarda_permanente"
                    :texto="__('Guarda permanente')"/>


                <x-form.input
                    wire:key="caixa-complemento"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="caixa.complemento"
                    wire:target="sugerirNumeroCaixa,store"
                    editavel
                    :erro="$errors->first('caixa.complemento')"
                    icone="quote"
                    maxlength="50"
                    :placeholder="__('Complemento do número da caixa')"
                    :texto="__('Complemento')"
                    :title="__('Informe um complemento para a numeração da caixa')"
                    type="text"
                    com_contador/>


                <div class="flex items-end space-x-2">

                    <x-form.input
                        wire:key="caixa-ano"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="caixa.ano"
                        wire:target="sugerirNumeroCaixa,store"
                        editavel
                        :erro="$errors->first('caixa.ano')"
                        icone="calendar-range"
                        min="1900"
                        :max="now()->format('Y')"
                        placeholder="aaaa"
                        required
                        :texto="__('Ano')"
                        :title="__('Informe o ano no padrão aaaa')"
                        type="number"/>


                    <div>

                        <x-button-icone
                            wire:click="sugerirNumeroCaixa"
                            wire:key="btn-sugerir-numero-caixa"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            class="btn-acao-alternativo"
                            icone="magic"
                            :title="__('Sugere o número da próxima caixa')"
                            type="button"/>

                    </div>

                </div>


                <x-form.input
                    wire:key="caixa-numero"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="caixa.numero"
                    wire:target="sugerirNumeroCaixa,store"
                    editavel
                    :erro="$errors->first('caixa.numero')"
                    icone="tag"
                    min="1"
                    :placeholder="__('Apenas números')"
                    required
                    :texto="__('Número')"
                    :title="__('Informe o número da caixa')"
                    type="number"/>


                <x-form.input
                    wire:key="caixa-volumes"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="volumes"
                    wire:target="store"
                    editavel
                    :erro="$errors->first('volumes')"
                    icone="collection"
                    min="1"
                    max="1000"
                    :placeholder="__('Apenas números')"
                    required
                    :texto="__('Qtd de volumes')"
                    :title="__('Informe o número de volumes das caixas')"
                    type="number"/>


                @can (\App\Enums\Policy::CreateMany->value, \App\Models\Caixa::class)

                    <x-form.input
                        wire:key="caixa-quantidade-can"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="quantidade"
                        wire:target="store"
                        editavel
                        :erro="$errors->first('quantidade')"
                        icone="collection"
                        min="1"
                        max="1000"
                        :placeholder="__('Apenas números')"
                        required
                        :texto="__('Quantidade')"
                        :title="__('Informe a quantidade de caixas para criar de uma vez')"
                        type="number"/>

                @else

                    <x-form.input
                        wire:key="caixa-quantidade-cannot"
                        wire:model.defer="quantidade"
                        :erro="$errors->first('quantidade')"
                        icone="collection"
                        min="1"
                        max="1"
                        :placeholder="__('Apenas números')"
                        required
                        :texto="__('Quantidade')"
                        :title="__('Informe a quantidade de caixas para criar de uma vez')"
                        type="number"/>

                @endcan

            </div>


            <x-form.textarea
                wire:key="caixa-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="caixa.descricao"
                wire:target="store"
                editavel
                :erro="$errors->first('caixa.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a caixa')"
                :texto="__('Descrição')"
                :title="__('Descreva a caixa')"
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

        <x-table.model.caixa
            :caixas="$this->caixas"
            :preferencias="$this->preferencias"
            :excluir="$this->excluir"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir/>

    </x-container>

</x-page>
