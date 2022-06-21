{{--
    View livewire for individual creation of shelf.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New shelves')">

    <x-backtrace :model="$this->stand" :root="true"/>


    <x-container>

        <form wire:key="form-shelf" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                    <x-show-value
                        :key="__('Site')"
                        :value="$this->stand->site_name"/>


                    <x-show-value
                        :key="__('Building')"
                        :value="$this->stand->building_name"/>

                </div>


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-3">

                    <x-show-value
                        :key="__('Floor')"
                        :value="$this->stand->floor_number"/>


                    <x-show-value
                        :key="__('Room')"
                        :value="$this->stand->room_number"/>


                    <x-show-value
                        :key="__('Stand')"
                        :value="$this->stand->for_humans"/>

                </div>


                <x-form.input
                    wire:key="shelf-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="shelf.number"
                    wire:target="store"
                    autofocus
                    :error="$errors->first('shelf.number')"
                    icon="list-nested"
                    min="1"
                    max="100000"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Shelf')"
                    :title="__('Inform the shelf number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="shelf-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="shelf.description"
                    wire:target="store"
                    :error="$errors->first('shelf.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the shelf')"
                    :text="__('Description')"
                    :title="__('Describes the shelf')"
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

        <x-table.model.shelf
            :deleting="$this->deleting"
            :shelves="$this->shelves"
            :sort_column="$this->sort_column"
            :sort_direction="$this->sort_direction"
            withdeletebutton/>

    </x-container>

</x-page>
