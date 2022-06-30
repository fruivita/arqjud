{{--
    View livewire for individual creation of building.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New buildings')">

    <x-backtrace :model="$this->site" :root="true"/>


    <x-container>

        <form wire:key="form-building" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <x-show-value
                    :key="__('Site')"
                    :value="$this->site->name"/>


                <x-form.input
                    wire:key="building-name"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="building.name"
                    wire:target="store"
                    autofocus
                    editavel
                    :error="$errors->first('building.name')"
                    icon="building"
                    maxlength="100"
                    :placeholder="__('Building name')"
                    required
                    :text="__('Building')"
                    :title="__('Inform the building name')"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:key="building-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="building.description"
                    wire:target="store"
                    editavel
                    :error="$errors->first('building.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the building')"
                    :text="__('Description')"
                    :title="__('Describes the building')"
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

        <x-table.model.building
            :buildings="$this->buildings"
            :deleting="$this->deleting"
            :preferencias="$this->preferencias"
            :sorts="$this->sorts"
            withdeletebutton/>

    </x-container>

</x-page>
