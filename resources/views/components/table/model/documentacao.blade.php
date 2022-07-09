{{--
    View Livewire para listagem da documentação da aplicação.

    Props:
    - excluir: objeto para ser excluído
    - documentacoes: coleção de documentação da aplicação que serão exibidas
    - preferencias: array de preferencias do usuário
    - ordenacoes: array associativo de colunas e direções usadas para ordenação
    - com_botao_novo: boolean se o botão novo deve ser exibido
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
    'excluir',
    'documentacoes',
    'preferencias',
    'ordenacoes' => [],
    'com_botao_novo' => false,
    'pesquisa_ativa' => false,
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $com_botao_novo == true
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Documentacao::class)
        )

            <x-link-button
                class="btn-acao w-full md:w-auto"
                icone="plus-circle"
                :href="route('administracao.documentacao.create')"
                :texto="__('Nova documentação de rota')"
                :title="__('Criar um novo registro')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-app-url"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="app_url"
                :texto="__('Nome da rota')"
                value="app_url"/>


            <x-form.checkbox
                wire:key="checkbox-documentacao-url"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="doc_url"
                :texto="__('Link da documentação')"
                value="doc_url"/>


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

        <x-table wire:key="table-documentacoes" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="ordenarPor('app_link')"
                    wire:key="checkbox-app-url"
                    :direcao="$ordenacoes['app_link'] ?? null"
                    :exibir="in_array('app_url', $preferencias['colunas'])"
                    :pesquisa_ativa="$pesquisa_ativa"
                    ordenavel
                >

                    {{ __('Nome da rota') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="ordenarPor('doc_link')"
                    wire:key="checkbox-doc-url"
                    :direcao="$ordenacoes['doc_link'] ?? null"
                    :exibir="in_array('doc_url', $preferencias['colunas'])"
                    :pesquisa_ativa="$pesquisa_ativa"
                    ordenavel
                >

                    {{ __('Link da documentação') }}

                </x-table.heading>


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Ações') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($documentacoes ?? [] as $documentacao)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('app_url', $preferencias['colunas'])">{{ $documentacao->app_link }}</x-table.cell>


                        <x-table.cell :exibir="in_array('doc_url', $preferencias['colunas'])">{{ $documentacao->doc_link }}</x-table.cell>


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-grupo-button-acao>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Documentacao::class)

                                    <x-link-button-icone
                                        class="btn-acao"
                                        icone="eye"
                                        :href="route('administracao.documentacao.edit', $documentacao)"
                                        :title="__('Exibir o registro')"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, \App\Models\Documentacao::class)

                                    <x-button-icone
                                        wire:click="marcarParaExcluir({{ $documentacao->id }})"
                                        wire:key="btn-delete-{{ $documentacao->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-perigo w-full"
                                        icone="trash"
                                        :title="__('Excluir o registro')"
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


    <x-links-paginacao :itens="$documentacoes"/>

</div>


@if(
    isset($excluir->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, \App\Models\Documentacao::class)
)

    {{-- Modal  para confirmar a excluisão do item --}}
    <x-modal-confirmacao
        wire:click="destroy"
        wire:key="modal-exclusao-{{ $excluir->id }}"
        wire:model="exibir_modal_exclusao"
        :pergunta="__('Excluir :attribute?', ['attribute' => $excluir->app_link])"/>

@endif
