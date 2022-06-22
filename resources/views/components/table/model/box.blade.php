{{--
    Livewire view for listing boxes.

    Props:
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
    'deleting' => null,
    'parent' => null,
    'sorts' => [],
    'withdeletebutton' => false,
    'withnewbutton' => false,
    'withparents' => false
])


<div class="space-y-3">

    <div class="flex items-center justify-between">

        @if(
            $withnewbutton == true
            && isset($parent)
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Box::class)
        )

            <x-link-button
                class="btn-do"
                icon="plus-circle"
                :href="route('archiving.register.box.create', $parent->id)"
                :text="__('New box')"
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

        <x-table wire:key="table-boxes" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('number')"
                    :direction="$sorts['number'] ?? null"
                    sortable
                >

                    {{ __('Box') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('year')"
                    :direction="$sorts['year'] ?? null"
                    sortable
                >

                    {{ __('Year') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('volumes_count')"
                    :direction="$sorts['volumes_count'] ?? null"
                    sortable
                >

                    {{ __('Qty of volumes') }}

                </x-table.heading>


                @if ($withparents)

                    <x-table.heading
                        wire:click="sortBy('sites.name')"
                        :direction="$sorts['sites.name'] ?? null"
                        sortable
                    >

                        {{ __('Site') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('buildings.name')"
                        :direction="$sorts['buildings.name'] ?? null"
                        sortable
                    >

                        {{ __('Building') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('floors.number')"
                        :direction="$sorts['floors.number'] ?? null"
                        sortable
                    >

                        {{ __('Floor') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('rooms.number')"
                        :direction="$sorts['rooms.number'] ?? null"
                        sortable
                    >

                        {{ __('Room') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('stands.number')"
                        :direction="$sorts['stands.number'] ?? null"
                        sortable
                    >

                        {{ __('Stand') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('shelves.number')"
                        :direction="$sorts['shelves.number'] ?? null"
                        sortable
                    >

                        {{ __('Shelf') }}

                    </x-table.heading>

                @endif


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($boxes ?? [] as $box)

                    <x-table.row>

                        <x-table.cell>{{ $box->number }}</x-table.cell>


                        <x-table.cell>{{ $box->year }}</x-table.cell>


                        <x-table.cell>{{ $box->volumes_count }}</x-table.cell>


                        @if ($withparents)

                            <x-table.cell>{{ $box->site_name }}</x-table.cell>


                            <x-table.cell>{{ $box->building_name }}</x-table.cell>


                            <x-table.cell>{{ $box->floor_number }}</x-table.cell>


                            <x-table.cell>{{ $box->room_number }}</x-table.cell>


                            <x-table.cell>{{ $box->stand_for_humans }}</x-table.cell>


                            <x-table.cell>{{ $box->shelf_for_humans }}</x-table.cell>

                        @endif


                        <x-table.cell>

                            <x-action-button-group>

                                @can(\App\Enums\Policy::View->value, \App\Models\Box::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.box.show', $box->id)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Box::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="pencil-square"
                                        :href="route('archiving.register.box.edit', $box->id)"
                                        :title="__('Edit the record')"/>

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

                        @if ($withparents)

                            <x-table.cell colspan="10">{{ __('No record found') }}</x-table.cell>

                        @else

                            <x-table.cell colspan="4">{{ __('No record found') }}</x-table.cell>

                        @endif

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
