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


    <x-container class="space-y-6">

        <div class="space-y-6">

            <x-show-value
                :key="__('Building')"
                :value="$building->name"/>


            <x-show-value
                :key="__('Description')"
                :value="$building->description"/>


            <x-show-value
                :key="__('Site')"
                :value="$building->site->name"/>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Building::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.building.edit', $building)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan


                <x-link-button
                    class="btn-do"
                    icon="building"
                    :href="route('archiving.register.building.index')"
                    :text="__('Buildings')"
                    :title="__('Show all records')"/>

            </x-button-group>


            <div class="overflow-x-auto">

                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    class="mb-3"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-floors" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Floor') }}</x-table.heading>


                        <x-table.heading>{{ __('Qty of rooms') }}</x-table.heading>


                        <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $floors ?? [] as $floor )

                            <x-table.row>

                                <x-table.cell>{{ $floor->number }}</x-table.cell>


                                <x-table.cell>{{ $floor->rooms_count }}</x-table.cell>


                                <x-table.cell>

                                    <x-action-button-group>

                                        @can(\App\Enums\Policy::View->value, \App\Models\Floor::class)

                                            <x-link-button
                                                class="btn-do"
                                                icon="eye"
                                                :href="route('archiving.register.floor.show', $floor)"
                                                :text="__('Show')"
                                                :title="__('Show the record')"/>

                                        @endcan


                                        @can(\App\Enums\Policy::Update->value, \App\Models\Floor::class)

                                            <x-link-button
                                                class="btn-do"
                                                icon="pencil-square"
                                                :href="route('archiving.register.floor.edit', $floor)"
                                                :text="__('Edit')"
                                                :title="__('Edit the record')"/>

                                        @endcan

                                    </x-action-button-group>

                                </x-table.cell>

                            </x-table.row>

                        @empty

                            <x-table.row>

                                <x-table.cell colspan="3">{{ __('No record found') }}</x-table.cell>

                            </x-table.row>

                        @endforelse

                    </x-slot>

                </x-table>

            </div>

        </div>

    </x-container>


    {{ $floors->links() }}

</x-page>
