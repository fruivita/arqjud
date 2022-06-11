{{--
    Livewire view for listing shelves.

    Props:
    - shelves: shelves that will be displayed
    - deleting: item to be deleted
    - parent: parent element of the item that will eventually be created
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
    'shelves' => $shelves,
    'deleting' => null,
    'parent' => null,
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
                :href="route('archiving.register.shelf.create', $parent)"
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

                <x-table.heading>{{ __('Shelf') }}</x-table.heading>


                <x-table.heading>{{ __('Qty of boxes') }}</x-table.heading>


                @if ($withparents)

                    <x-table.heading>{{ __('Site') }}</x-table.heading>


                    <x-table.heading>{{ __('Building') }}</x-table.heading>


                    <x-table.heading>{{ __('Floor') }}</x-table.heading>


                    <x-table.heading>{{ __('Room') }}</x-table.heading>


                    <x-table.heading>{{ __('Stand') }}</x-table.heading>

                @endif


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($shelves ?? [] as $shelf)

                    <x-table.row>

                        <x-table.cell>{{ $shelf->number }}</x-table.cell>


                        <x-table.cell>{{ $shelf->boxes_count }}</x-table.cell>


                        @if ($withparents)

                            <x-table.cell>{{ $shelf->stand->room->floor->building->site->name }}</x-table.cell>


                            <x-table.cell>{{ $shelf->stand->room->floor->building->name }}</x-table.cell>


                            <x-table.cell>{{ $shelf->stand->room->floor->number }}</x-table.cell>


                            <x-table.cell>{{ $shelf->stand->room->number }}</x-table.cell>


                            <x-table.cell>{{ $shelf->stand->number }}</x-table.cell>

                        @endif


                        <x-table.cell>

                            <x-action-button-group>

                                @can(\App\Enums\Policy::View->value, \App\Models\Shelf::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.shelf.show', $shelf)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Shelf::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="pencil-square"
                                        :href="route('archiving.register.shelf.edit', $shelf)"
                                        :title="__('Edit the record')"/>

                                @endcan


                                @if(
                                    $withdeletebutton == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $shelf)
                                )

                                    <x-icon-button
                                        wire:click="markToDelete({{ $shelf->id }})"
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
    $withdeletebutton == true
    && auth()->user()->can(\App\Enums\Policy::Delete->value, $deleting)
)

    {{-- Modal to confirm the deletion --}}
    <x-confirmation-modal
        wire:model="show_delete_modal"
        wire:key="deleting-modal-{{ $deleting->id }}"
        wire:submit.prevent="destroy"
        :question="__('Delete :attribute?', ['attribute' => $deleting->number])"/>

@endif
