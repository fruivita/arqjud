{{--
    View livewire para delegação de perfil.

    A delegação se opera sobre a entidade perfil e não nas permissões, isto é
    o delegado receberá o mesmo perfil do delegante e, consequentemente, as
    mesmas permissões.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Delegação')">

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

                <x-table wire:key="tabela-delegacoes" wire:loading.delay.class="opacity-25">

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

                        @forelse ($this->delegaveis ?? [] as $usuario)

                            <x-table.row>

                                <x-table.cell :exibir="in_array('nome', $preferencias['colunas'])">{{ $usuario->nome }}</x-table.cell>


                                <x-table.cell :exibir="in_array('usuario', $preferencias['colunas'])">{{ $usuario->username }}</x-table.cell>


                                <x-table.cell :exibir="in_array('perfil', $preferencias['colunas'])">{{ $usuario->perfil->nome }}</x-table.cell>


                                <x-table.cell :exibir="in_array('delegante', $preferencias['colunas'])">{{ optional($usuario->delegante)->username }}</x-table.cell>


                                <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                                    <x-grupo-button-acao>

                                        @can(\App\Enums\Policy::DelegacaoDelete->value, [$usuario])

                                            <x-button
                                                wire:click="destroy({{ $usuario->id }})"
                                                wire:key="delegacao-destroy-{{ $usuario->id }}"
                                                class="btn-perigo w-full"
                                                icone="x-circle"
                                                :texto="__('Revogar')"
                                                :title="__('Revogar as permissões do usuário')"
                                                type="button"/>

                                        @elsecan(\App\Enums\Policy::DelegacaoCreate->value, [$usuario])

                                            <x-button
                                                wire:click="create({{ $usuario->id }})"
                                                wire:key="delegacao-create-{{ $usuario->id }}"
                                                class="btn-acao w-full"
                                                icone="check-circle"
                                                :texto="__('Conceder')"
                                                :title="__('Conceder minhas permissões ao usuário')"
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


            <x-links-paginacao :itens="$this->delegaveis"/>

        </div>

    </x-container>

</x-page>
