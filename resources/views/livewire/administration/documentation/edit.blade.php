{{--
    View livewire for individual editing of application routes documentation.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the route documentation')">

    <x-container>

        <form wire:key="form-doc" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="doc-app-route-name"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="doc.app_route_name"
                    wire:target="update"
                    autofocus
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('doc.app_route_name')"
                    icon="signpost-2"
                    maxlength="255"
                    :placeholder="__('example.create.index')"
                    required
                    :text="__('Route name')"
                    :title="__('Inform the route name')"
                    type="text"
                    withcounter/>


                <x-form.input
                    wire:key="doc-link"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="doc.doc_link"
                    wire:target="update"
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('doc.doc_link')"
                    icon="link"
                    maxlength="255"
                    :placeholder="__('http://example.com/')"
                    :text="__('Documentation link')"
                    :title="__('Inform the link to the documentation of the route informed')"
                    type="text"
                    withcounter/>


                    @can(\App\Enums\Policy::Update->value, \App\Models\Documentation::class)

                        <x-button-group>

                            <x-form.edit-save-cancel :modo_edicao="$this->modo_edicao"/>

                        </x-button-group>

                    @endcan

            </div>

        </form>

    </x-container>

</x-page>
