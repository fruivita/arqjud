{{--
    View Livewire para listagem das localidades.

    Props:
    - excluir: objeto para ser excluído
    - preferencias: array de preferencias do usuário
    - localidades: coleção de localidades para exibição
    - ordenacoes: array associativo de colunas e direções usadas para ordenação
    - com_botao_novo: boolean se o botão novo deve ser exibido

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'excluir',
    'preferencias',
    'localidades',
    'ordenacoes' => [],
    'com_botao_novo' => false,
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $com_botao_novo == true
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Localidade::class)
        )

            <x-link-button
                class="btn-acao w-full md:w-auto"
                icone="plus-circle"
                :href="route('arquivamento.cadastro.localidade.create')"
                :texto="__('Novas localidade')"
                :title="__('Criar um novo registro')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

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
                wire:key="checkbox-qtd-predios"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="qtd_predios"
                :texto="__('Qtd de prédios')"
                value="qtd_predios"/>


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

        <x-table wire:key="tabela-localidades" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="ordenarPor('nome')"
                    wire:key="ordenar-nome"
                    :direcao="$ordenacoes['nome'] ?? null"
                    :exibir="in_array('localidade', $preferencias['colunas'])"
                    ordenavel
                >

                    {{ __('Localidade') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="ordenarPor('predios_count')"
                    wire:key="ordenar-predios"
                    :direcao="$ordenacoes['predios_count'] ?? null"
                    :exibir="in_array('qtd_predios', $preferencias['colunas'])"
                    ordenavel
                >

                    {{ __('Qtd de prédios') }}

                </x-table.heading>


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Ações') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($localidades ?? [] as $localidade)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('localidade', $preferencias['colunas'])">{{ $localidade->nome }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_predios', $preferencias['colunas'])">{{ $localidade->predios_count }}</x-table.cell>


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-grupo-button-acao>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Localidade::class)

                                    <x-link-button-icone
                                        class="btn-acao"
                                        icone="eye"
                                        :href="route('arquivamento.cadastro.localidade.edit', $localidade)"
                                        :title="__('Exibir o registro')"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, $localidade)

                                    <x-button-icone
                                        wire:click="marcarParaExcluir({{ $localidade->id }})"
                                        wire:key="btn-delete-{{ $localidade->id }}"
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


    <x-links-paginacao :itens="$localidades"/>

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
        :pergunta="__('Excluir a localidade :attribute?', ['attribute' => $excluir->nome])"/>

@endif
