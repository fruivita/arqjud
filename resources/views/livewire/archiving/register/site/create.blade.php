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

        <form wire:key="form-site" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="site-name"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="site.name"
                    wire:target="store"
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
