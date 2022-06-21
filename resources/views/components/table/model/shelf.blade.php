{{--
    Livewire view for listing shelves.

    Props:
    - deleting: item to be deleted
    - parent: parent element of the item that will eventually be created
    - shelves: shelves that will be displayed
    - sort_column: column used to sort
    - sort_direction: column sort direction
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
    'deleting' => null,
    'parent' => null,
    'shelves',
    'sort_column' => null,
    'sort_direction' => null,
    'withdeletebutton' => false,
    'withnewbutton' => false,
    'withparents' => false
])


<div class="space-y-3">

    <div class="flex items-center justify-between">

        @if(
            $withnewbutton == true
            && isset($parent)
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Shelf::class)
        )

            <x-link-button
                class="btn-do"
                icon="plus-circle"
                :href="route('archiving.register.shelf.create', $parent->id)"
                :text="__('New')"
                :title="__('Create a new record')"/>

        @else

            <div></div>

        @endif


        <x-perpage
            wire:key="per-page"
            wire:model="per_page"
            :error="$errors->first('per_page')"/>

    </div>


    <div class="overflow-x-auto">

        <x-table wire:key="table-shelves" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('number')"
                    :direction="$sort_column === 'number' ? $sort_direction : null"
                    sortable
                >

                    {{ __('Shelf') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('boxes_count')"
                    :direction="$sort_column === 'boxes_count' ? $sort_direction : null"
                    sortable
                >

                    {{ __('Qty of boxes') }}

                </x-table.heading>


                @if ($withparents)

                    <x-table.heading
                        wire:click="sortBy('sites.name')"
                        :direction="$sort_column === 'sites.name' ? $sort_direction : null"
                        sortable
                    >

                        {{ __('Site') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('buildings.name')"
                        :direction="$sort_column === 'buildings.name' ? $sort_direction : null"
                        sortable
                    >

                        {{ __('Building') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('floors.number')"
                        :direction="$sort_column === 'floors.number' ? $sort_direction : null"
                        sortable
                    >

                        {{ __('Floor') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('rooms.number')"
                        :direction="$sort_column === 'rooms.number' ? $sort_direction : null"
                        sortable
                    >

                        {{ __('Room') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('stands.number')"
                        :direction="$sort_column === 'stands.number' ? $sort_direction : null"
                        sortable
                    >

                        {{ __('Stand') }}

                    </x-table.heading>

                @endif


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($shelves ?? [] as $shelf)

                    <x-table.row>

                        <x-table.cell>{{ $shelf->for_humans }}</x-table.cell>


                        <x-table.cell>{{ $shelf->boxes_count }}</x-table.cell>


                        @if ($withparents)

                            <x-table.cell>{{ $shelf->site_name }}</x-table.cell>


                            <x-table.cell>{{ $shelf->building_name }}</x-table.cell>


                            <x-table.cell>{{ $shelf->floor_number }}</x-table.cell>


                            <x-table.cell>{{ $shelf->room_number }}</x-table.cell>


                            <x-table.cell>{{ $shelf->stand_for_humans }}</x-table.cell>

                        @endif


                        <x-table.cell>

                            <x-action-button-group>

                                @can(\App\Enums\Policy::View->value, \App\Models\Shelf::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.shelf.show', $shelf->id)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Shelf::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="pencil-square"
                                        :href="route('archiving.register.shelf.edit', $shelf->id)"
                                        :title="__('Edit the record')"/>

                                @endcan


                                @if(
                                    $withdeletebutton == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $shelf)
                                )

                                    <x-icon-button
                                        wire:click="setToDelete({{ $shelf->id }})"
                                        wire:key="btn-delete-{{ $shelf->id }}"
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

                        @if ($withparents)

                            <x-table.cell colspan="8">{{ __('No record found') }}</x-table.cell>

                        @else

                            <x-table.cell colspan="3">{{ __('No record found') }}</x-table.cell>

                        @endif

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </div>


    {{ $shelves->links() }}

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
        :question="__('Delete :attribute?', ['attribute' => $deleting->for_humans])"/>

@endif
