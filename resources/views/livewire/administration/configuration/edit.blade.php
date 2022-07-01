{{--
    View livewire for individual configuration editing.

    Available settings:
    - Superadmin: User with full, non-delegable and non-removable permissions.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the application settings')">

    <x-container>

        <div class="space-y-6">

            <div class="lg:inline-flex">

                <x-form.input
                    wire:key="configuration-superadmin"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="configuration.superadmin"
                    wire:target="update"
                    autocomplete="off"
                    autofocus
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('configuration.superadmin')"
                    icon="person"
                    maxlength="20"
                    :placeholder="__('Ldap user')"
                    required
                    :text="__('New super adminitrator')"
                    :title="__('Inform a network user')"
                    type="text"
                    withcounter/>

            </div>


            @can(\App\Enums\Policy::Update->value, \App\Models\Site::class)

                <x-button-group>

                    <x-form.edit-save-cancel :modo_edicao="$this->modo_edicao"/>

                </x-button-group>

            @endcan

        </div>

    </x-container>

</x-page>
