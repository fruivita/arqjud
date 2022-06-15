{{--
    View livewire for individual floor display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Floor') . ': ' . $floor->number">

    <x-backtrace :model="$floor"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Floor')"
                :value="$floor->number"/>


            <x-show-value
                :key="__('Description')"
                :value="$floor->description"/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-show-value
                    :key="__('Site')"
                    :value="$floor->building->site->name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$floor->building->name"/>

            </div>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Floor::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.floor.edit', $floor)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.room
            :rooms="$rooms"
            :parent="$floor"
            withnewbutton/>

    </x-container>

</x-page>
