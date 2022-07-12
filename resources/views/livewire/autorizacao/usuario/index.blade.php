{{--
    View para atualização dos perfis do usuário.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Usuários e perfil')">

    <x-pesquisa
        wire:key="pesquisar"
        wire:model.debounce.500ms="termo"
        :erro="$errors->first('termo')"
        com_contador/>


    <x-container>

        <div class="space-y-3">

            <x-table.topo-tabela>

                <div></div>


                <x-table.acoes-tabela>

                    <x-form.checkbox
                        wire:key="checkbox-nome"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="preferencias.colunas"
                        editavel
                        name="nome"
                        :texto="__('Nome')"
                        value="nome"/>


                    <x-form.checkbox
                        wire:key="checkbox-usuario"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="preferencias.colunas"
                        editavel
                        name="usuario"
                        :texto="__('Usuário')"
                        value="usuario"/>


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
                        wire:key="checkbox-delegante"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="preferencias.colunas"
                        editavel
                        name="delegante"
                        :texto="__('Delegante')"
                        value="delegante"/>


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

                <x-table wire:key="tabela-usuarios" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading
                            wire:click="ordenarPor('nome')"
                            wire:key="ordenar-nome"
                            :direcao="$ordenacoes['nome'] ?? null"
                            :exibir="in_array('nome', $preferencias['colunas'])"
                            :pesquisa_ativa="$this->termo ? true : false"
                            ordenavel
                        >

                            {{ __('Nome') }}

                        </x-table.heading>


                        <x-table.heading
                            wire:click="ordenarPor('username')"
                            wire:key="ordenar-username"
                            :direcao="$ordenacoes['username'] ?? null"
                            :exibir="in_array('usuario', $preferencias['colunas'])"
                            :pesquisa_ativa="$this->termo ? true : false"
                            ordenavel
                        >

                            {{ __('Usuário') }}

                        </x-table.heading>


                        <x-table.heading :exibir="in_array('perfil', $preferencias['colunas'])">{{ __('Perfil') }}</x-table.heading>


                        <x-table.heading :exibir="in_array('delegante', $preferencias['colunas'])">{{ __('Delegante') }}</x-table.heading>


                        <x-table.heading
                            class="w-10"
                            :exibir="in_array('acoes', $preferencias['colunas'])"
                        >

                            {{ __('Ações') }}

                        </x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ($this->usuarios ?? [] as $usuario)

                            <x-table.row>

                                <x-table.cell :exibir="in_array('nome', $preferencias['colunas'])">{{ $usuario->nome }}</x-table.cell>


                                <x-table.cell :exibir="in_array('usuario', $preferencias['colunas'])">{{ $usuario->username }}</x-table.cell>


                                <x-table.cell :exibir="in_array('perfil', $preferencias['colunas'])">{{ $usuario->perfil->nome }}</x-table.cell>


                                <x-table.cell :exibir="in_array('delegante', $preferencias['colunas'])">{{ optional($usuario->delegante)->username }}</x-table.cell>


                                <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                                    <x-grupo-button-acao>

                                        @can (\App\Enums\Policy::Update->value, \App\Models\Usuario::class)

                                            <x-button-icone
                                                wire:click="edit({{ $usuario->id }})"
                                                wire:key="edit-button-{{ $usuario->id }}"
                                                wire:loading.delay.attr="disabled"
                                                wire:loading.delay.class="cursor-not-allowed"
                                                class="btn-acao"
                                                icone="pencil-square"
                                                :title="__('Editar o registro')"
                                                type="button"/>

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

            </div>


        <x-links-paginacao :itens="$this->usuarios"/>

        </div>

    </x-container>


    @can (\App\Enums\Policy::Update->value, \App\Models\Usuario::class)

        <x-modal
            wire:key="modal-edicao-usuario-{{ $this->em_edicao->id }}"
            wire:model="exibir_modal_edicao"
        >

            <x-slot name="titulo">{{ $this->em_edicao->username . ' ' . $this->em_edicao->nome }}</x-slot>


            <x-slot name="conteudo">

                <div wire:key="wrapper-edicao-usuario-{{ $this->em_edicao->id }}" wire:loading.delay.class="opacity-25">

                    <x-form.select
                        wire:key="edicao-usuario-{{ $this->em_edicao->id }}"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="em_edicao.perfil_id"
                        editavel
                        :erro="$errors->first('em_edicao.perfil_id')"
                        icone="award"
                        required
                        :texto="__('Perfil')"
                        :title="__('Escolha o perfil')">

                        @foreach ($this->perfis ?? [] as $perfil)

                            <option value="{{ $perfil->id }}">

                                {{ $perfil->nome }}

                            </option>

                        @endforeach

                    </x-form.select>

                </div>

            </x-slot>


            <x-slot name="rodape">

                <x-feedback.inline/>


                <x-button
                    wire:click="update"
                    wire:key="btn-update"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    class="btn-acao"
                    icone="save"
                    :texto="__('Salvar')"
                    :title="__('Salvar o registro')"
                    type="button"/>

            </x-slot>

        </x-modal>

    @endcan

</x-page>
