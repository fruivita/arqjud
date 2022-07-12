{{--
    View Livewire para listagem dos volumes da caixa.

    Props:
    - excluir: objeto para ser excluído
    - preferencias: array de preferencias do usuário
    - ordenacoes: array associativo de colunas e direções usadas para ordenação
    - volumes: coleção de volumes de caixa para exibição
    - com_botao_excluir: boolean se o botão excluir deve ser exibido
    - com_botao_novo: boolean se o botão novo deve ser exibido

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props ([
    'excluir' => null,
    'preferencias',
    'ordenacoes' => [],
    'volumes',
    'com_botao_excluir' => false,
    'com_botao_novo' => false,
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if (
            $com_botao_novo == true
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\VolumeCaixa::class)
        )

            <x-button
                wire:click="storeVolume()"
                wire:key="btn-store-volume"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:target="predio_id,andar_id,sala_id,estante_id,localidade_id,storeVolume,update"
                class="btn-acao w-full md:w-auto"
                icone="plus-circle"
                :texto="__('Novas volume')"
                :title="__('Criar um novo registro')"
                type="button"/>


            {{-- exibição de eventual mensagem de erro --}}
            @error ('volume') <x-erro>{{ $message }}</x-erro> @enderror

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-volume"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="volume"
                :texto="__('Volume')"
                value="volume"/>


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

        <x-table wire:key="tabela-volumes-caixa" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="ordenarPor('numero')"
                    wire:key="ordenar-numero"
                    :direcao="$ordenacoes['numero'] ?? null"
                    :exibir="in_array('volume', $preferencias['colunas'])"
                    ordenavel
                >

                    {{ __('Volume') }}

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
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Ações') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($volumes ?? [] as $volume)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('volume', $preferencias['colunas'])">{{ $volume->numero }}</x-table.cell>


                        <x-table.cell :exibir="in_array('apelido', $preferencias['colunas'])">{{ $volume->apelido }}</x-table.cell>


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-grupo-button-acao>

                                @if (
                                    $com_botao_excluir == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, \App\Models\VolumeCaixa::class)
                                )

                                    <x-button-icone
                                        wire:click="marcarParaExcluir({{ $volume->id }})"
                                        wire:key="btn-delete-{{ $volume->id }}"
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


    <x-links-paginacao :itens="$volumes"/>

</div>


@if (
    isset($excluir->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, \App\Models\VolumeCaixa::class)
)

    {{-- Modal  para confirmar a excluisão do item --}}
    <x-modal-confirmacao
        wire:click="destroy"
        wire:key="modal-exclusao-{{ $excluir->id }}"
        wire:model="exibir_modal_exclusao"
        :pergunta="__('Excluir o volume :attribute?', ['attribute' => $excluir->numero])"/>

@endif
