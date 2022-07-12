{{--
    View Livewire para listagem das caixas.

    Props:
    - caixas: coleção de caixas para exibição
    - excluir: objeto para ser excluído
    - pai: objeto pai do item que será, eventualmente, criado
    - preferencias: array de preferencias do usuário
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


@props ([
    'caixas',
    'excluir' => null,
    'pai' => null,
    'preferencias',
    'ordenacoes' => [],
    'com_botao_excluir' => false,
    'com_botao_novo' => false,
    'com_pais' => false,
    'pesquisa_ativa' => false,
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if (
            $com_botao_novo == true
            && isset($pai)
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Caixa::class)
        )

            <x-link-button
                class="btn-acao w-full md:w-auto"
                icone="plus-circle"
                :href="route('arquivamento.cadastro.caixa.create', $pai->id)"
                :texto="__('Novas caixa')"
                :title="__('Criar um novo registro')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-caixa"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="caixa"
                :texto="__('Caixa')"
                value="caixa"/>


            <x-form.checkbox
                wire:key="checkbox-ano"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="ano"
                :texto="__('Ano')"
                value="ano"/>


            <x-form.checkbox
                wire:key="checkbox-qtd-volumes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="qtd_volumes"
                :texto="__('Qtd de volumes')"
                value="qtd_volumes"/>


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
                    wire:key="checkbox-estante"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="estante"
                    :texto="__('Estante')"
                    value="estante"/>


                <x-form.checkbox
                    wire:key="checkbox-prateleira"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="prateleira"
                    :texto="__('Prateleira')"
                    value="prateleira"/>

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

        <x-table wire:key="tabela-caixas" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="ordenarPor('numero')"
                    wire:key="ordenar-numero"
                    :direcao="$ordenacoes['numero'] ?? null"
                    :exibir="in_array('caixa', $preferencias['colunas'])"
                    :pesquisa_ativa="$pesquisa_ativa"
                    ordenavel
                >

                    {{ __('Caixa') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="ordenarPor('ano')"
                    wire:key="ordenar-ano"
                    :direcao="$ordenacoes['ano'] ?? null"
                    :exibir="in_array('ano', $preferencias['colunas'])"
                    :pesquisa_ativa="$pesquisa_ativa"
                    ordenavel
                >

                    {{ __('Ano') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="ordenarPor('volumes_count')"
                    wire:key="ordenar-volumes"
                    :direcao="$ordenacoes['volumes_count'] ?? null"
                    :exibir="in_array('qtd_volumes', $preferencias['colunas'])"
                    ordenavel
                >

                    {{ __('Qtd de volumes') }}

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


                    <x-table.heading
                        wire:click="ordenarPor('salas.numero')"
                        wire:key="ordenar-salas"
                        :direcao="$ordenacoes['salas.numero'] ?? null"
                        :exibir="in_array('sala', $preferencias['colunas'])"
                        ordenavel
                    >

                        {{ __('Sala') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="ordenarPor('estantes.numero')"
                        wire:key="ordenar-estantes"
                        :direcao="$ordenacoes['estantes.numero'] ?? null"
                        :exibir="in_array('estante', $preferencias['colunas'])"
                        ordenavel
                    >

                        {{ __('Estante') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="ordenarPor('prateleiras.numero')"
                        wire:key="ordenar-prateleiras"
                        :direcao="$ordenacoes['prateleiras.numero'] ?? null"
                        :exibir="in_array('prateleira', $preferencias['colunas'])"
                        ordenavel
                    >

                        {{ __('Prateleira') }}

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

                @forelse ($caixas ?? [] as $caixa)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('caixa', $preferencias['colunas'])">{{ $caixa->numero }}</x-table.cell>


                        <x-table.cell :exibir="in_array('ano', $preferencias['colunas'])">{{ $caixa->ano }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_volumes', $preferencias['colunas'])">{{ $caixa->volumes_count }}</x-table.cell>


                        @if ($com_pais)

                            <x-table.cell :exibir="in_array('localidade', $preferencias['colunas'])">{{ $caixa->localidade_nome }}</x-table.cell>


                            <x-table.cell :exibir="in_array('predio', $preferencias['colunas'])">{{ $caixa->predio_nome }}</x-table.cell>


                            <x-table.cell :exibir="in_array('andar', $preferencias['colunas'])">{{ $caixa->andar_apelido }}</x-table.cell>


                            <x-table.cell :exibir="in_array('sala', $preferencias['colunas'])">{{ $caixa->sala_numero }}</x-table.cell>


                            <x-table.cell :exibir="in_array('estante', $preferencias['colunas'])">{{ $caixa->estante_para_humano }}</x-table.cell>


                            <x-table.cell :exibir="in_array('prateleira', $preferencias['colunas'])">{{ $caixa->prateleira_para_humano }}</x-table.cell>

                        @endif


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-grupo-button-acao>

                                @can (\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Caixa::class)

                                    <x-link-button-icone
                                        class="btn-acao"
                                        icone="eye"
                                        :href="route('arquivamento.cadastro.caixa.edit', $caixa->id)"
                                        :title="__('Exibir o registro')"/>

                                @endcan


                                @if (
                                    $com_botao_excluir == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $caixa)
                                )

                                    <x-button-icone
                                        wire:click="marcarParaExcluir({{ $caixa->id }})"
                                        wire:key="btn-delete-{{ $caixa->id }}"
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


    <x-links-paginacao :itens="$caixas"/>

</div>


@if (
    isset($excluir->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, $excluir)
)

    {{-- Modal  para confirmar a excluisão do item --}}
    <x-modal-confirmacao
        wire:click="destroy"
        wire:key="modal-exclusao-{{ $excluir->id }}"
        wire:model="exibir_modal_exclusao"
        :pergunta="__('Excluir a caixa :attribute?', ['attribute' => $excluir->para_humano])"/>

@endif
