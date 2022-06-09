{{--
    View livewire for individual creation of room.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New rooms')">

    <x-backtrace :model="$floor" :root="true"/>


    <x-container>

        <form wire:key="form-room" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <x-show-value
                    :key="__('Site')"
                    :value="$floor->building->site->name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$floor->building->name"/>


                <x-show-value
                    :key="__('Floor')"
                    :value="$floor->number"/>


                <x-form.input
                    wire:key="room-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="room.number"
                    wire:target="store"
                    :error="$errors->first('room.number')"
                    icon="layers"
                    min="1"
                    max="100000"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Room')"
                    :title="__('Inform the room number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="room-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="room.description"
                    wire:target="store"
                    :error="$errors->first('room.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the room')"
                    :text="__('Description')"
                    :title="__('Describes the room')"
                    withcounter/>


                <x-button-group>

                    <x-feedback.inline/>


                    <x-button
                        class="btn-do"
                        icon="save"
                        :text="__('Save')"
                        :title="__('Save the record')"
                        type="submit"/>

                </x-button-group>

            </div>

        </form>

    </x-container>


    <x-container>

        <x-perpage
            wire:key="per-page"
            wire:model="per_page"
            class="mb-3"
            :error="$errors->first('per_page')"/>


        <x-table wire:key="table-rooms" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Room') }}</x-table.heading>


                <x-table.heading>{{ __('Qty of stands') }}</x-table.heading>


                <x-table.heading>{{ __('Site') }}</x-table.heading>


                <x-table.heading>{{ __('Building') }}</x-table.heading>


                <x-table.heading>{{ __('Floor') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($rooms ?? [] as $room)

                    <x-table.row>

                        <x-table.cell>{{ $room->number }}</x-table.cell>


                        <x-table.cell>{{ $room->stands_count }}</x-table.cell>


                        <x-table.cell>{{ $room->floor->building->site->name }}</x-table.cell>


                        <x-table.cell>{{ $room->floor->building->name }}</x-table.cell>


                        <x-table.cell>{{ $room->floor->number }}</x-table.cell>


                        <x-table.cell>

                            <x-action-button-group>

                                @can(\App\Enums\Policy::View->value, \App\Models\Room::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.room.show', $room)"
                                        :text="__('Show')"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Room::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="pencil-square"
                                        :href="route('archiving.register.room.edit', $room)"
                                        :text="__('Edit')"
                                        :title="__('Edit the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, $room)

                                    <x-button
                                        wire:click="markToDelete({{ $room->id }})"
                                        wire:key="btn-delete-{{ $room->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger w-full"
                                        icon="trash"
                                        :text="__('Delete')"
                                        :title="__('Delete the record')"
                                        type="button"/>

                                @endcan

                            </x-action-button-group>

                        </x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="6">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </x-container>


    {{ $rooms->links() }}


    @can(\App\Enums\Policy::Delete->value, $deleting)

        {{-- Modal to confirm the deletion --}}
        <x-confirmation-modal
            wire:model="show_delete_modal"
            wire:key="deleting-modal-{{ $deleting->id }}"
            wire:submit.prevent="destroy"
            :question="__('Delete :attribute?', ['attribute' => $deleting->number])"/>

    @endcan

</x-page>
