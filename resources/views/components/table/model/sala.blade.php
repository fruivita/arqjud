{{--
    View Livewire para listagem das salas.

    Props:
    - excluir: objeto para ser excluído
    - pai: objeto pai do item que será, eventualmente, criado
    - preferencias: array de preferencias do usuário
    - salas: coleção de salas para exibição
    - ordenacoes: array associativo de colunas e direções usadas para ordenação
    - com_botao_excluir: boolean se o botão excluir deve ser exibido
    - com_botao_novo: boolean se o botão novo deve ser exibido
    - com_pais: boolean se as informações sobre os pais devem ser exibidos
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
    'excluir' => null,
    'pai' => null,
    'preferencias',
    'salas',
    'ordenacoes' => [],
    'com_botao_excluir' => false,
    'com_botao_novo' => false,
    'com_pais' => false,
    'pesquisa_ativa' => false,
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $com_botao_novo == true
            && isset($pai)
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Sala::class)
        )

            <x-link-button
                class="btn-acao w-full md:w-auto"
                icone="plus-circle"
                :href="route('arquivamento.cadastro.sala.create', $pai->id)"
                :texto="__('Novas sala')"
                :title="__('Criar um novo registro')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-sala"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="sala"
                :texto="__('Sala')"
                value="sala"/>


            <x-form.checkbox
                wire:key="checkbox-qtd-estantes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="qtd_estantes"
                :texto="__('Qtd de estantes')"
                value="qtd_estantes"/>


            @if ($com_pais)

                <x-form.checkbox
                    wire:key="checkbox-localidade"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="localidade"
                    :texto="__('Localidade')"
                    value="localidade"/>


                <x-form.checkbox
                    wire:key="checkbox-predio"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="predio"
                    :texto="__('Prédio')"
                    value="predio"/>


                <x-form.checkbox
                    wire:key="checkbox-andar"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="andar"
                    :texto="__('Andar')"
                    value="andar"/>

            @endif


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

        <x-table wire:key="tabela-salas" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="ordenarPor('numero')"
                    wire:key="ordenar-numero"
                    :direcao="$ordenacoes['numero'] ?? null"
                    :exibir="in_array('sala', $preferencias['colunas'])"
                    :pesquisa_ativa="$pesquisa_ativa"
                    ordenavel
                >

                    {{ __('Sala') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="ordenarPor('estantes_count')"
                    wire:key="ordenar-estantes"
                    :direcao="$ordenacoes['estantes_count'] ?? null"
                    :exibir="in_array('qtd_estantes', $preferencias['colunas'])"
                    ordenavel
                >

                    {{ __('Qtd de estantes') }}

                </x-table.heading>


                @if ($com_pais)

                    <x-table.heading
                        wire:click="ordenarPor('localidades.nome')"
                        wire:key="ordenar-localidades"
                        :direcao="$ordenacoes['localidades.nome'] ?? null"
                        :exibir="in_array('localidade', $preferencias['colunas'])"
                        ordenavel
                    >

                        {{ __('Localidade') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="ordenarPor('predios.nome')"
                        wire:key="ordenar-predios"
                        :direcao="$ordenacoes['predios.nome'] ?? null"
                        :exibir="in_array('predio', $preferencias['colunas'])"
                        ordenavel
                    >

                        {{ __('Prédio') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="ordenarPor('andares.apelido')"
                        wire:key="ordenar-andares"
                        :direcao="$ordenacoes['andares.apelido'] ?? null"
                        :exibir="in_array('andar', $preferencias['colunas'])"
                        ordenavel
                    >

                        {{ __('Andar') }}

                    </x-table.heading>

                @endif


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Ações') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ( $salas ?? [] as $sala )

                    <x-table.row>

                        <x-table.cell :exibir="in_array('sala', $preferencias['colunas'])">{{ $sala->numero }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_estantes', $preferencias['colunas'])">{{ $sala->estantes_count }}</x-table.cell>


                        @if ($com_pais)

                            <x-table.cell :exibir="in_array('localidade', $preferencias['colunas'])">{{ $sala->localidade_nome }}</x-table.cell>


                            <x-table.cell :exibir="in_array('predio', $preferencias['colunas'])">{{ $sala->predio_nome }}</x-table.cell>


                            <x-table.cell :exibir="in_array('andar', $preferencias['colunas'])">{{ $sala->andar_apelido }}</x-table.cell>

                        @endif


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-grupo-button-acao>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Sala::class)

                                    <x-link-button-icone
                                        class="btn-acao"
                                        icone="eye"
                                        :href="route('arquivamento.cadastro.sala.edit', $sala->id)"
                                        :title="__('Exibir o registro')"/>

                                @endcan


                                @if(
                                    $com_botao_excluir == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $sala)
                                )

                                    <x-button-icone
                                        wire:click="marcarParaExcluir({{ $sala->id }})"
                                        wire:key="btn-delete-{{ $sala->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-perigo w-full"
                                        icone="trash"
                                        :title="__('Excluir o registro')"
                                        type="button"/>

                                @endif

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


    <x-links-paginacao :itens="$salas"/>

</div>


@if(
    isset($excluir->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, $excluir)
)

    {{-- Modal  para confirmar a excluisão do item --}}
    <x-modal-confirmacao
        wire:click="destroy"
        wire:key="modal-exclusao-{{ $excluir->id }}"
        wire:model="exibir_modal_exclusao"
        :pergunta="__('Excluir a sala :attribute?', ['attribute' => $excluir->numero])"/>

@endif
