{{--
    View livewire for individual creation of floor.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New floors')">

    <x-backtrace :model="$this->building" :root="true"/>


    <x-container>

        <div class="space-y-6">

            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-show-value
                    :key="__('Site')"
                    :value="$this->building->site_name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$this->building->name"/>


                <x-form.input
                    wire:key="floor-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="floor.number"
                    wire:target="store"
                    autofocus
                    editavel
                    :error="$errors->first('floor.number')"
                    icon="layers"
                    min="-100"
                    max="300"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Floor')"
                    :title="__('Inform the floor number')"
                    type="number"/>


                <x-form.input
                    wire:key="floor-alias"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="floor.alias"
                    wire:target="store"
                    editavel
                    :error="$errors->first('floor.alias')"
                    icon="symmetry-vertical"
                    maxlength="100"
                    :placeholder="__('Suggestion: Garage, G1, Ground floor, 10th...')"
                    :text="__('Alias')"
                    :title="__('Inform the floor alias')"
                    type="text"
                    withcounter/>

            </div>


            <x-form.textarea
                wire:key="floor-description"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="floor.description"
                wire:target="store"
                editavel
                :error="$errors->first('floor.description')"
                icon="blockquote-left"
                maxlength="255"
                :placeholder="__('About the floor')"
                :text="__('Description')"
                :title="__('Describes the floor')"
                withcounter/>


            <x-button-group>

                <x-feedback.inline/>


                <x-button
                    wire:click="store"
                    wire:key="btn-store"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    class="btn-do"
                    icon="save"
                    :text="__('Save')"
                    :title="__('Save the record')"
                    type="button"/>

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.floor
            :deleting="$this->deleting"
            :floors="$this->floors"
            :preferencias="$this->preferencias"
            :sorts="$this->sorts"
            withdeletebutton/>

    </x-container>

</x-page>
