{{--
    Livewire view for listing box volumes.

    Props:
    - deleting: item to be deleted
    - preferencias: array de preferencias do usuário
    - sorts: columns and directions used to sort
    - volumes: box volumes that will be displayed
    - withdeletebutton: whether the delete button should be displayed
    - withnewbutton: whether the new button should be displayed

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'deleting' => null,
    'preferencias',
    'sorts' => [],
    'volumes',
    'withdeletebutton' => false,
    'withnewbutton' => false,
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $withnewbutton == true
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\BoxVolume::class)
        )

            <x-button
                wire:click="storeVolume()"
                wire:key="btn-store-volume"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:target="building_id,floor_id,room_id,stand_id,site_id,storeVolume,update"
                class="btn-do w-full md:w-auto"
                icon="plus-circle"
                :text="__('New volume')"
                :title="__('Create a new record')"
                type="button"/>


            <x-error>{{ $errors->first('volume') }}</x-error>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-volume"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                name="volume"
                :text="__('Volume')"
                value="volume"/>


            <x-form.checkbox
                wire:key="checkbox-apelido"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                name="apelido"
                :text="__('Alias')"
                value="apelido"/>


            <x-form.checkbox
                wire:key="checkbox-acoes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                name="acoes"
                :text="__('Actions')"
                value="acoes"/>

        </x-table.acoes-tabela>

    </x-table.topo-tabela>


    <div class="overflow-x-auto">

        <x-table wire:key="table-volumes" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('number')"
                    :direction="$sorts['number'] ?? null"
                    :exibir="in_array('volume', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Volume') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('alias')"
                    :direction="$sorts['alias'] ?? null"
                    :exibir="in_array('apelido', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Alias') }}

                </x-table.heading>


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Actions') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($volumes ?? [] as $volume)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('volume', $preferencias['colunas'])">{{ $volume->number }}</x-table.cell>


                        <x-table.cell :exibir="in_array('apelido', $preferencias['colunas'])">{{ $volume->alias }}</x-table.cell>


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-action-button-group>

                                @if(
                                    $withdeletebutton == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, \App\Models\BoxVolume::class)
                                )

                                    <x-icon-button
                                        wire:click="setToDelete({{ $volume->id }})"
                                        wire:key="btn-delete-{{ $volume->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger w-full"
                                        icon="trash"
                                        :title="__('Delete the record')"
                                        type="button"/>

                                @endif

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


    <x-links-paginacao :itens="$volumes"/>

</div>


@if(
    isset($deleting->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, \App\Models\BoxVolume::class)
)

    {{-- Modal to confirm the deletion --}}
    <x-confirmation-modal
        wire:click="destroy"
        wire:key="deleting-modal-{{ $deleting->id }}"
        wire:model="show_delete_modal"
        :question="__('Delete volume :attribute?', ['attribute' => $deleting->number])"/>

@endif
