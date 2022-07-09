{{--
    View livewire para listagem dos perfis.

    Props:
    - limit: int quantidade limite de itens filhos utilizada
    - preferencias: array de preferencias do usuário
    - perfis: coleção de perfis da aplicação que serão exibidos
    - ordenacoes: array associativo de colunas e direções usadas para ordenação
    - pesquisa_ativa: boolean se o resultado está filtrado devido à pesquisa do
    usuário.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'limite',
    'preferencias',
    'perfis',
    'ordenacoes' => [],
    'pesquisa_ativa' => false,
])


<div class="space-y-3">

    <x-table.topo-tabela>

        <div></div>


        <x-table.acoes-tabela>

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
                wire:key="checkbox-permissoes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="permissoes"
                :texto="__('Permissões')"
                value="permissoes"/>


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

                <x-table.heading
                    wire:click="ordenarPor('nome')"
                    wire:key="ordenar-nome"
                    :direcao="$ordenacoes['nome'] ?? null"
                    :exibir="in_array('perfil', $preferencias['colunas'])"
                    :pesquisa_ativa="$pesquisa_ativa"
                    ordenavel
                >

                    {{ __('Perfil') }}

                </x-table.heading>


                <x-table.heading :exibir="in_array('permissoes', $preferencias['colunas'])">

                    {{ __('Permissões') }}

                </x-table.heading>


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Ações') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($perfis ?? [] as $perfil)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('perfil', $preferencias['colunas'])">{{ $perfil->nome }}</x-table.cell>


                        <x-table.cell :exibir="in_array('permissoes', $preferencias['colunas'])">

                            <ul class="divide-y divide-primaria-200 dark:divide-secundaria-600">

                                @forelse ($perfil->permissoes ?? [] as $permissao)

                                    <li>{{ $permissao->nome }}</li>


                                    @if ($loop->last && $perfil->permissoes->count() == $limite)

                                        <li class="font-bold text-right">{{ __('Podem existir mais') }}</li>

                                    @endif

                                @empty

                                    <li>{{ __('Nenhum registro encontrado') }}</li>

                                @endforelse

                            </ul>

                        </x-table.cell>


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-grupo-button-acao>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Perfil::class)

                                    <x-link-button-icone
                                        class="btn-acao"
                                        icone="eye"
                                        :href="route('autorizacao.perfil.edit', $perfil)"
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

    </div>


    <x-links-paginacao :itens="$perfis"/>

</div>
