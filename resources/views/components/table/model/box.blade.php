{{--
    Livewire view for listing boxes.

    Props:
    - colunas: colunas da tabela que devem ser exibidas
    - boxes: boxes that will be displayed
    - deleting: item to be deleted
    - parent: parent element of the item that will eventually be created
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
    'boxes',
    'colunas',
    'deleting' => null,
    'parent' => null,
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
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Box::class)
        )

            <x-link-button
                class="btn-do w-full md:w-auto"
                icon="plus-circle"
                :href="route('archiving.register.box.create', $parent->id)"
                :text="__('New box')"
                :title="__('Create a new record')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-caixa"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="caixa"
                :text="__('Box')"
                value="caixa"/>


            <x-form.checkbox
                wire:key="checkbox-ano"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="ano"
                :text="__('Year')"
                value="ano"/>


            <x-form.checkbox
                wire:key="checkbox-qtd-volumes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="qtd_volumes"
                :text="__('Qty of volumes')"
                value="qtd_volumes"/>


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


                <x-form.checkbox
                    wire:key="checkbox-sala"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="sala"
                    :text="__('Room')"
                    value="sala"/>


                <x-form.checkbox
                    wire:key="checkbox-estante"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="estante"
                    :text="__('Stand')"
                    value="estante"/>


                <x-form.checkbox
                    wire:key="checkbox-prateleira"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="prateleira"
                    :text="__('Shelf')"
                    value="prateleira"/>

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

        <x-table wire:key="table-boxes" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('number')"
                    :direction="$sorts['number'] ?? null"
                    :exibir="in_array('caixa', $colunas)"
                    sortable
                >

                    {{ __('Box') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('year')"
                    :direction="$sorts['year'] ?? null"
                    :exibir="in_array('ano', $colunas)"
                    sortable
                >

                    {{ __('Year') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('volumes_count')"
                    :direction="$sorts['volumes_count'] ?? null"
                    :exibir="in_array('qtd_volumes', $colunas)"
                    sortable
                >

                    {{ __('Qty of volumes') }}

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


                    <x-table.heading
                        wire:click="sortBy('rooms.number')"
                        :direction="$sorts['rooms.number'] ?? null"
                        :exibir="in_array('sala', $colunas)"
                        sortable
                    >

                        {{ __('Room') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('stands.number')"
                        :direction="$sorts['stands.number'] ?? null"
                        :exibir="in_array('estante', $colunas)"
                        sortable
                    >

                        {{ __('Stand') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('shelves.number')"
                        :direction="$sorts['shelves.number'] ?? null"
                        :exibir="in_array('prateleira', $colunas)"
                        sortable
                    >

                        {{ __('Shelf') }}

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

                @forelse ($boxes ?? [] as $box)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('caixa', $colunas)">{{ $box->number }}</x-table.cell>


                        <x-table.cell :exibir="in_array('ano', $colunas)">{{ $box->year }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_volumes', $colunas)">{{ $box->volumes_count }}</x-table.cell>


                        @if ($withparents)

                            <x-table.cell :exibir="in_array('localidade', $colunas)">{{ $box->site_name }}</x-table.cell>


                            <x-table.cell :exibir="in_array('predio', $colunas)">{{ $box->building_name }}</x-table.cell>


                            <x-table.cell :exibir="in_array('andar', $colunas)">{{ $box->floor_alias }}</x-table.cell>


                            <x-table.cell :exibir="in_array('sala', $colunas)">{{ $box->room_number }}</x-table.cell>


                            <x-table.cell :exibir="in_array('estante', $colunas)">{{ $box->stand_for_humans }}</x-table.cell>


                            <x-table.cell :exibir="in_array('prateleira', $colunas)">{{ $box->shelf_for_humans }}</x-table.cell>

                        @endif


                        <x-table.cell :exibir="in_array('acoes', $colunas)">

                            <x-action-button-group>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Box::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.box.edit', $box->id)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @if(
                                    $withdeletebutton == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $box)
                                )

                                    <x-icon-button
                                        wire:click="setToDelete({{ $box->id }})"
                                        wire:key="btn-delete-{{ $box->id }}"
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


    {{ $boxes->links() }}

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
        :question="__('Delete box :attribute?', ['attribute' => $deleting->for_humans])"/>

@endif
