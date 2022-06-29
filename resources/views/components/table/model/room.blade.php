{{--
    Livewire view for listing rooms.

    Props:
    - colunas: colunas da tabela que devem ser exibidas
    - deleting: item to be deleted
    - parent: parent element of the item that will eventually be created
    - rooms: rooms that will be displayed
    - sorts: columns and directions used to sort
    - withdeletebutton: whether the delete button should be displayed
    - withnewbutton: whether the new button should be displayed
    - withparents: whether the parent info should be displayed

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'colunas',
    'deleting' => null,
    'parent' => null,
    'rooms',
    'sorts' => [],
    'withdeletebutton' => false,
    'withnewbutton' => false,
    'withparents' => false
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $withnewbutton == true
            && isset($parent)
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Room::class)
        )

            <x-link-button
                class="btn-do w-full md:w-auto"
                icon="plus-circle"
                :href="route('archiving.register.room.create', $parent->id)"
                :text="__('New room')"
                :title="__('Create a new record')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-sala"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="sala"
                :text="__('Room')"
                value="sala"/>


            <x-form.checkbox
                wire:key="checkbox-qtd-estantes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="qtd_estantes"
                :text="__('Qty of stands')"
                value="qtd_estantes"/>


            @if ($withparents)

                <x-form.checkbox
                    wire:key="checkbox-localidade"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="localidade"
                    :text="__('Site')"
                    value="localidade"/>


                <x-form.checkbox
                    wire:key="checkbox-predio"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="predio"
                    :text="__('Building')"
                    value="predio"/>


                <x-form.checkbox
                    wire:key="checkbox-andar"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="andar"
                    :text="__('Floor')"
                    value="andar"/>

            @endif


            <x-form.checkbox
                wire:key="checkbox-acoes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="acoes"
                :text="__('Actions')"
                value="acoes"/>

        </x-table.acoes-tabela>

    </x-table.topo-tabela>


    <div class="overflow-x-auto">

        <x-table wire:key="table-rooms" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('number')"
                    :direction="$sorts['number'] ?? null"
                    :exibir="in_array('sala', $colunas)"
                    sortable
                >

                    {{ __('Room') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('stands_count')"
                    :direction="$sorts['stands_count'] ?? null"
                    :exibir="in_array('qtd_estantes', $colunas)"
                    sortable
                >

                    {{ __('Qty of stands') }}

                </x-table.heading>


                @if ($withparents)

                    <x-table.heading
                        wire:click="sortBy('sites.name')"
                        :direction="$sorts['sites.name'] ?? null"
                        :exibir="in_array('localidade', $colunas)"
                        sortable
                    >

                        {{ __('Site') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('buildings.name')"
                        :direction="$sorts['buildings.name'] ?? null"
                        :exibir="in_array('predio', $colunas)"
                        sortable
                    >

                        {{ __('Building') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('floors.alias')"
                        :direction="$sorts['floors.alias'] ?? null"
                        :exibir="in_array('andar', $colunas)"
                        sortable
                    >

                        {{ __('Floor') }}

                    </x-table.heading>

                @endif


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $colunas)"
                >

                    {{ __('Actions') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ( $rooms ?? [] as $room )

                    <x-table.row>

                        <x-table.cell :exibir="in_array('sala', $colunas)">{{ $room->number }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_estantes', $colunas)">{{ $room->stands_count }}</x-table.cell>


                        @if ($withparents)

                            <x-table.cell :exibir="in_array('localidade', $colunas)">{{ $room->site_name }}</x-table.cell>


                            <x-table.cell :exibir="in_array('predio', $colunas)">{{ $room->building_name }}</x-table.cell>


                            <x-table.cell :exibir="in_array('andar', $colunas)">{{ $room->floor_alias }}</x-table.cell>

                        @endif


                        <x-table.cell :exibir="in_array('acoes', $colunas)">

                            <x-action-button-group>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Room::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.room.edit', $room->id)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @if(
                                    $withdeletebutton == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $room)
                                )

                                    <x-icon-button
                                        wire:click="setToDelete({{ $room->id }})"
                                        wire:key="btn-delete-{{ $room->id }}"
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

                        <x-table.cell colspan="{{ count($colunas) }}">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </div>


    {{ $rooms->links() }}

</div>


@if(
    isset($deleting->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, $deleting)
)

    {{-- Modal to confirm the deletion --}}
    <x-confirmation-modal
        wire:model="show_delete_modal"
        wire:key="deleting-modal-{{ $deleting->id }}"
        wire:submit.prevent="destroy"
        :question="__('Delete room :attribute?', ['attribute' => $deleting->number])"/>

@endif
