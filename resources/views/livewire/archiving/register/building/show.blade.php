{{--
    View livewire for individual building display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Building') . ': ' . $building->name">

    <x-backtrace :model="$building"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Building')"
                :value="$building->name"/>


            <x-show-value
                :key="__('Description')"
                :value="$building->description"/>


            <x-show-value
                :key="__('Site')"
                :value="$building->site_name"/>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Building::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.building.edit', $building)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.floor
            :floors="$floors"
            :parent="$building"
            :sort_column="$sort_column"
            :sort_direction="$sort_direction"
            withnewbutton/>

    </x-container>

</x-page>
