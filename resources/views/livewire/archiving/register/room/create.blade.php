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

    <x-backtrace :model="$this->floor" :root="true"/>


    <x-container>

        <form wire:key="form-room" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                    <x-show-value
                        :key="__('Site')"
                        :value="$this->floor->site_name"/>


                    <x-show-value
                        :key="__('Building')"
                        :value="$this->floor->building_name"/>

                </div>


                <x-show-value
                    :key="__('Floor')"
                    :value="$this->floor->number"/>


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

        <x-table.model.room
            :deleting="$this->deleting"
            :rooms="$this->rooms"
            :sort_column="$this->sort_column"
            :sort_direction="$this->sort_direction"
            withdeletebutton/>

    </x-container>

</x-page>
