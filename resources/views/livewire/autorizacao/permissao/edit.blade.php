{{--
    View livewire para edição individual da permissão.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Editar a permissão')">

    <x-container>

        <div class="space-y-6">

            <x-form.input
                wire:key="permissao-nome"
                wire:model.defer="permissao.nome"
                autofocus
                :editavel="$this->modo_edicao"
                :erro="$errors->first('permissao.nome')"
                icone="vector-pen"
                maxlength="50"
                :placeholder="__('Nome da permissão')"
                required
                :texto="__('Nome')"
                :title="__('Informe o nome da permissão')"
                type="text"
                com_contador/>


            <x-form.textarea
                wire:key="permissao-descricao"
                wire:model.defer="permissao.descricao"
                :editavel="$this->modo_edicao"
                :erro="$errors->first('permissao.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a permissão')"
                :texto="__('Descrição')"
                :title="__('Descreva a permissão')"
                com_contador/>


            <div class="space-y-3">

                <x-table.topo-tabela>

                    <div></div>


                    <x-table.acoes-tabela>

                        <x-form.checkbox
                            wire:key="checkbox-seletores"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="preferencias.colunas"
                            editavel
                            name="seletores"
                            :texto="__('Seletores')"
                            value="seletores"/>


                        <x-form.checkbox
                            wire:key="checkbox-perfil"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="preferencias.colunas"
                            editavel
                            name="perfil"
                            :texto="__('Perfil')"
                            value="perfil"/>


                        <x-form.checkbox
                            wire:key="checkbox-descricao"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="preferencias.colunas"
                            editavel
                            name="descricao"
                            :texto="__('Descrição')"
                            value="descricao"/>


                        <x-form.checkbox
                            wire:key="checkbox-acoes"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="preferencias.colunas"
                            editavel
                            name="acoes"
                            :texto="__('Ações')"
                            value="acoes"/>

                    </x-table.acoes-tabela>

                </x-table.topo-tabela>


                <div class="overflow-x-auto">

                    <x-table wire:key="tabela-perfis" wire:loading.delay.class="opacity-25">

                        <x-slot name="head">

                            <x-table.heading class="text-left" :exibir="in_array('seletores', $preferencias['colunas'])">

                                @if ($this->modo_edicao)

                                    <x-table.seletor-checkbox
                                        wire:key="seletor-checkbox"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        wire:target="update"
                                        wire:model="acao_checkbox"/>


                                @endif

                            </x-table.heading>


                            <x-table.heading
                                wire:click="ordenarPor('nome')"
                                wire:key="ordenar-nome"
                                :direcao="$ordenacoes['nome'] ?? null"
                                :exibir="in_array('perfil', $preferencias['colunas'])"
                                ordenavel
                            >

                                {{ __('Perfil') }}

                            </x-table.heading>


                            <x-table.heading
                                wire:click="ordenarPor('descricao')"
                                wire:key="ordenar-descricao"
                                :direcao="$ordenacoes['descricao'] ?? null"
                                :exibir="in_array('descricao', $preferencias['colunas'])"
                                ordenavel
                            >

                                {{ __('Descrição') }}

                            </x-table.heading>


                            <x-table.heading
                                class="w-10"
                                :exibir="in_array('acoes', $preferencias['colunas'])"
                            >

                                {{ __('Ações') }}

                            </x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            <x-table.row wire:key="linha-contador-selecao">

                                <x-table.cell class="text-left" colspan="{{ count($preferencias['colunas']) }}">

                                    <p>

                                        <span class="font-bold">

                                            {{ __(':attribute registros selecionados de :total', ['attribute' => is_array($this->selecionados) ? count($this->selecionados) : 0, 'total' => $this->perfis->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $this->perfis ?? [] as $perfil )

                                <x-table.row wire:key="linha-{{ $perfil->id }}">

                                    <x-table.cell :exibir="in_array('seletores', $preferencias['colunas'])">

                                        <x-form.checkbox
                                            wire:key="checkbox-perfil-{{ $perfil->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            wire:model="selecionados"
                                            :selecionado="$this->permissao->perfis->contains($perfil->id)"
                                            :editavel="$this->modo_edicao"
                                            :value="$perfil->id"/>

                                    </x-table.cell>


                                    <x-table.cell :exibir="in_array('perfil', $preferencias['colunas'])">{{ $perfil->nome }}</x-table.cell>


                                    <x-table.cell :exibir="in_array('descricao', $preferencias['colunas'])">{{ $perfil->descricao }}</x-table.cell>


                                    <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                                        <x-grupo-button-acao>

                                            @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Perfil::class)

                                                <x-link-button-icone
                                                    class="btn-acao"
                                                    icone="eye"
                                                    :href="route('autorizacao.perfil.edit', $perfil->id)"
                                                    :title="__('Exibir o registro')"/>

                                            @endcan

                                        </x-grupo-button-acao>

                                    </x-table.cell>

                                </x-table.row>

                            @empty

                                <x-table.row>

                                    <x-table.cell colspan="{{ count($preferencias['colunas']) }}">{{ __('Nenhum registro encontrado') }}</x-table.cell>

                                </x-table.row>

                            @endforelse

                        </x-slot>

                    </x-table>


                    {{-- exibição de eventual mensagem de erro --}}
                    @error('selecionados') <x-erro>{{ $message }}</x-erro> @enderror

                </div>


                @can(\App\Enums\Policy::Update->value, \App\Models\Permissao::class)

                    <x-grupo-button>

                        <x-form.button-editar-salvar-cancelar :modo_edicao="$this->modo_edicao"/>

                    </x-grupo-button>

                @endcan

            </div>

        </div>

    </x-container>


    <x-links-paginacao :itens="$this->perfis"/>

</x-page>
