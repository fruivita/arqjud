{{--
    View livewire for individual display of settings.

    Available settings:
    - Superadmin: User with full, non-delegable and non-removable permissions.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Application settings')">

    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Super administrator')"
                :value="$configuration->superadmin"/>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Configuration::class)


                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('administration.configuration.edit')"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>

</x-page>
