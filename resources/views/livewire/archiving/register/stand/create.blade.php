{{--
    View livewire for individual creation of stand.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New stands')">

    <x-backtrace :model="$this->room" :root="true"/>


    <x-container>

        <form wire:key="form-stand" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                    <x-show-value
                        :key="__('Site')"
                        :value="$this->room->site_name"/>


                    <x-show-value
                        :key="__('Building')"
                        :value="$this->room->building_name"/>


                    <x-show-value
                        :key="__('Floor')"
                        :value="$this->room->floor_number"/>


                    <x-show-value
                        :key="__('Room')"
                        :value="$this->room->number"/>


                    <x-form.input
                        wire:key="stand-number"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="stand.number"
                        wire:target="store"
                        autofocus
                        editavel
                        :error="$errors->first('stand.number')"
                        icon="bookshelf"
                        min="1"
                        max="100000"
                        :placeholder="__('Only numbers')"
                        required
                        :text="__('Stand')"
                        :title="__('Inform the stand number')"
                        type="number"/>


                    <x-form.input
                        wire:key="stand-alias"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="stand.alias"
                        wire:target="store"
                        editavel
                        :error="$errors->first('stand.alias')"
                        icon="symmetry-vertical"
                        maxlength="100"
                        :placeholder="__('Stand alias')"
                        :text="__('Alias')"
                        :title="__('Inform the stand alias')"
                        type="text"
                        withcounter/>

                </div>


                <x-form.textarea
                    wire:key="stand-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="stand.description"
                    wire:target="store"
                    editavel
                    :error="$errors->first('stand.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the stand')"
                    :text="__('Description')"
                    :title="__('Describes the stand')"
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

        <x-table.model.stand
            :deleting="$this->deleting"
            :stands="$this->stands"
            :sorts="$this->sorts"
            withdeletebutton/>

    </x-container>

</x-page>
