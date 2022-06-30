{{--
    View livewire for individual editing of sites.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the site')">

    <x-container>

        <form wire:key="form-site" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="site-name"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="site.name"
                    wire:target="update"
                    autofocus
                    :editavel="$this->modo_edicao"
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
                    wire:target="update"
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('site.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the site')"
                    :text="__('Description')"
                    :title="__('Describes the site')"
                    withcounter/>


                @can(\App\Enums\Policy::Update->value, \App\Models\Site::class)

                    <x-button-group>

                        <x-form.edit-save-cancel :modo_edicao="$this->modo_edicao"/>

                    </x-button-group>

                @endcan

            </div>

        </form>

    </x-container>


    <x-container>

        <x-table.model.building
            :buildings="$this->buildings"
            :deleting="$this->deleting"
            :parent="$this->site"
            :preferencias="$this->preferencias"
            :sorts="$this->sorts"
            withdeletebutton
            withnewbutton/>

    </x-container>

</x-page>
