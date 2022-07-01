{{--
    View livewire for individual creation of site.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New sites')">

    <x-container>

        <div class="space-y-6">

            <x-form.input
                wire:key="site-name"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="site.name"
                wire:target="store"
                autofocus
                editavel
                :error="$errors->first('site.name')"
                icon="pin-map"
                maxlength="100"
                :placeholder="__('Site name')"
                required
                :text="__('Site')"
                :title="__('Inform the site name')"
                type="text"
                withcounter/>


            <x-form.textarea
                wire:key="site-description"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="site.description"
                wire:target="store"
                editavel
                :error="$errors->first('site.description')"
                icon="blockquote-left"
                maxlength="255"
                :placeholder="__('About the site')"
                :text="__('Description')"
                :title="__('Describes the site')"
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

        <x-table.model.site
            :deleting="$this->deleting"
            :preferencias="$this->preferencias"
            :sites="$this->sites"
            :sorts="$this->sorts"/>

    </x-container>

</x-page>
