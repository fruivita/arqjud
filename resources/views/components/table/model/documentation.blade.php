{{--
    Livewire view for listing documentacao.

    Props:
    - deleting: item to be deleted
    - documentacao: array de documentos da aplicação que serão exibidos
    - preferencias: array de preferencias do usuário
    - sorts: columns and directions used to sort
    - withnewbutton: whether the new button should be displayed

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'deleting',
    'documentacao',
    'preferencias',
    'sorts' => [],
    'withnewbutton' => false,
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $withnewbutton == true
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Documentation::class)
        )

            <x-link-button
                class="btn-do w-full md:w-auto"
                icon="plus-circle"
                :href="route('administration.doc.create')"
                :text="__('New route documentation')"
                :title="__('Create a new record')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-nome-rota"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="nome_rota"
                :text="__('Route name')"
                value="nome_rota"/>


            <x-form.checkbox
                wire:key="checkbox-link-documentacao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="link_documentacao"
                :text="__('Documentation link')"
                value="link_documentacao"/>


            <x-form.checkbox
                wire:key="checkbox-acoes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="acoes"
                :text="__('Actions')"
                value="acoes"/>

        </x-table.acoes-tabela>

    </x-table.topo-tabela>


    <div class="overflow-x-auto">

        <x-table wire:key="table-docs" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('app_route_name')"
                    :direction="$sorts['app_route_name'] ?? null"
                    :exibir="in_array('nome_rota', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Route name') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('doc_link')"
                    :direction="$sorts['doc_link'] ?? null"
                    :exibir="in_array('link_documentacao', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Documentation link') }}

                </x-table.heading>


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Actions') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($documentacao ?? [] as $documento)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('nome_rota', $preferencias['colunas'])">{{ $documento->app_route_name }}</x-table.cell>


                        <x-table.cell :exibir="in_array('link_documentacao', $preferencias['colunas'])">{{ $documento->doc_link }}</x-table.cell>


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-action-button-group>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Documentation::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('administration.doc.edit', $documento)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, \App\Models\Documentation::class)

                                    <x-icon-button
                                        wire:click="setToDelete({{ $documento->id }})"
                                        wire:key="btn-delete-{{ $documento->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger w-full"
                                        icon="trash"
                                        :title="__('Delete the record')"
                                        type="button"/>

                                @endcan

                            </x-action-button-group>

                        </x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="{{ count($preferencias['colunas']) }}">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </div>


    <x-links-paginacao :itens="$documentacao"/>

</div>


@if(
    isset($deleting->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, \App\Models\Documentation::class)
)

    {{-- Modal to confirm the deletion --}}
    <x-confirmation-modal
        wire:click="destroy"
        wire:key="deleting-modal-{{ $deleting->id }}"
        wire:model="show_delete_modal"
        :question="__('Delete :attribute?', ['attribute' => $deleting->app_route_name])"/>

@endif
