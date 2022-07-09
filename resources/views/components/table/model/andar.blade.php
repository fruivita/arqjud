{{--
    View Livewire para listagem dos andares.

    Props:
    - excluir: objeto para ser excluído
    - andares: coleção de andares para exibição
    - pai: objeto pai do item que será, eventualmente, criado
    - preferencias: array de preferencias do usuário
    - ordenacoes: array associativo de colunas e direções usadas para ordenação
    - com_botao_excluir: boolean se o botão excluir deve ser exibido
    - com_botao_novo: boolean se o botão novo deve ser exibido
    - com_pais: boolean se as informações sobre os pais devem ser exibidos

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'excluir' => null,
    'andares',
    'pai' => null,
    'preferencias',
    'ordenacoes' => [],
    'com_botao_excluir' => false,
    'com_botao_novo' => false,
    'com_pais' => false
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $com_botao_novo == true
            && isset($pai)
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Andar::class)
        )

            <x-link-button
                class="btn-acao w-full md:w-auto"
                icone="plus-circle"
                :href="route('arquivamento.cadastro.andar.create', $pai->id)"
                :texto="__('Novas andar')"
                :title="__('Criar um novo registro')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-andar"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="andar"
                :texto="__('Andar')"
                value="andar"/>


            <x-form.checkbox
                wire:key="checkbox-apelido"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="apelido"
                :texto="__('Apelido')"
                value="apelido"/>


            <x-form.checkbox
                wire:key="checkbox-qtd-salas"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="qtd_salas"
                :texto="__('Qtd de salas')"
                value="qtd_salas"/>


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

        <x-table wire:key="tabela-andares" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="ordenarPor('numero')"
                    wire:key="ordenar-numero"
                    :direcao="$ordenacoes['numero'] ?? null"
                    :exibir="in_array('andar', $preferencias['colunas'])"
                    ordenavel
                >

                    {{ __('Andar') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="ordenarPor('apelido')"
                    wire:key="ordenar-apelido"
                    :direcao="$ordenacoes['apelido'] ?? null"
                    :exibir="in_array('apelido', $preferencias['colunas'])"
                    ordenavel
                >

                    {{ __('Apelido') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="ordenarPor('salas_count')"
                    wire:key="ordenar-salas"
                    :direcao="$ordenacoes['salas_count'] ?? null"
                    :exibir="in_array('qtd_salas', $preferencias['colunas'])"
                    ordenavel
                >

                    {{ __('Qtd de salas') }}

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

                @endif


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Ações') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ( $andares ?? [] as $andar )

                    <x-table.row>

                        <x-table.cell :exibir="in_array('andar', $preferencias['colunas'])">{{ $andar->numero }}</x-table.cell>


                        <x-table.cell :exibir="in_array('apelido', $preferencias['colunas'])">{{ $andar->apelido }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_salas', $preferencias['colunas'])">{{ $andar->salas_count }}</x-table.cell>


                        @if ($com_pais)

                            <x-table.cell :exibir="in_array('localidade', $preferencias['colunas'])">{{ $andar->localidade_nome }}</x-table.cell>


                            <x-table.cell :exibir="in_array('predio', $preferencias['colunas'])">{{ $andar->predio_nome }}</x-table.cell>

                        @endif


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-grupo-button-acao>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Andar::class)

                                    <x-link-button-icone
                                        class="btn-acao"
                                        icone="eye"
                                        :href="route('arquivamento.cadastro.andar.edit', $andar->id)"
                                        :title="__('Exibir o registro')"/>

                                @endcan


                                @if(
                                    $com_botao_excluir == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $andar)
                                )

                                    <x-button-icone
                                        wire:click="marcarParaExcluir({{ $andar->id }})"
                                        wire:key="btn-delete-{{ $andar->id }}"
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


    <x-links-paginacao :itens="$andares"/>

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
        :pergunta="__('Excluir o andar :attribute?', ['attribute' => $excluir->numero])"/>

@endif
