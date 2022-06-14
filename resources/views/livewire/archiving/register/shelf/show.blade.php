{{--
    View livewire for individual shelf display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Shelf') . ': ' . $shelf->numberForHumans()">

    <x-backtrace :model="$shelf"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Shelf')"
                :value="$shelf->numberForHumans()"/>


            <x-show-value
                :key="__('Description')"
                :value="$shelf->description"/>


            <x-show-value
                :key="__('Site')"
                :value="$shelf->stand->room->floor->building->site->name"/>


            <x-show-value
                :key="__('Building')"
                :value="$shelf->stand->room->floor->building->name"/>


            <x-show-value
                :key="__('Floor')"
                :value="$shelf->stand->room->floor->number"/>


            <x-show-value
                :key="__('Room')"
                :value="$shelf->stand->room->number"/>


            <x-show-value
                :key="__('Stand')"
                :value="$shelf->stand->numberForHumans()"/>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Shelf::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.shelf.edit', $shelf)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <div class="overflow-x-auto">

            <div class="flex items-center justify-between mb-3">

                @can(\App\Enums\Policy::Create->value, \App\Models\Box::class)

                    <x-link-button
                        class="btn-do"
                        icon="plus-circle"
                        :href="route('archiving.register.box.create', $shelf)"
                        :text="__('New')"
                        :title="__('Create a new record')"/>

                @else

                    <div></div>

                @endcan


                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    :error="$errors->first('per_page')"/>

            </div>


            <x-table wire:key="table-boxes" wire:loading.delay.class="opacity-25">

                <x-slot name="head">

                    <x-table.heading>{{ __('Box') }}</x-table.heading>


                    <x-table.heading>{{ __('Qty of volumes') }}</x-table.heading>


                    <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

                </x-slot>


                <x-slot name="body">

                    @forelse ( $boxes ?? [] as $box )

                        <x-table.row>

                            <x-table.cell>{{ $box->number }}</x-table.cell>


                            <x-table.cell>{{ $box->volumes_count }}</x-table.cell>


                            <x-table.cell>

                                <x-action-button-group>

                                    @can(\App\Enums\Policy::View->value, \App\Models\Box::class)

                                        <x-icon-link-button
                                            class="btn-do"
                                            icon="eye"
                                            :href="route('archiving.register.box.show', $box)"
                                            :title="__('Show the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Update->value, \App\Models\Box::class)

                                        <x-icon-link-button
                                            class="btn-do"
                                            icon="pencil-square"
                                            :href="route('archiving.register.box.edit', $box)"
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

    </x-container>


    {{ $boxes->links() }}

</x-page>
