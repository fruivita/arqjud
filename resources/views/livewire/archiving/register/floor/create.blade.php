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

    <x-container>

        <form wire:key="form-floor" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <x-show-value
                    :key="__('Site')"
                    :value="$building->site->name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$building->name"/>


                <x-form.input
                    wire:key="floor-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="floor.number"
                    wire:target="store"
                    :error="$errors->first('floor.number')"
                    icon="layers"
                    min="-100"
                    max="300"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Floor')"
                    :title="__('Inform the floor number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="floor-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="floor.description"
                    wire:target="store"
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
                        class="btn-do"
                        icon="save"
                        :text="__('Save')"
                        :title="__('Save the record')"
                        type="submit"/>

                </x-button-group>

            </div>

        </form>

    </x-container>

</x-page>
