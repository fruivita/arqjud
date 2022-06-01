{{--
    View livewire for boxes listing.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Boxes')">

    <x-search
        wire:key="search"
        wire:model.debounce.500ms="term"
        :error="$errors->first('term')"
        withcounter/>


    <x-container>

        <div class="flex items-center justify-between mb-3">

            @can(\App\Enums\Policy::Create->value, \App\Models\Box::class)

                <x-link-button
                    class="btn-do"
                    icon="plus-circle"
                    :href="route('archiving.register.box.create')"
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

                <x-table.heading>{{ __('Number') }}</x-table.heading>


                <x-table.heading>{{ __('Year') }}</x-table.heading>


                <x-table.heading>{{ __('Stand') }}</x-table.heading>


                <x-table.heading>{{ __('Shelf') }}</x-table.heading>


                <x-table.heading>{{ __('Volumes') }}</x-table.heading>


                <x-table.heading>{{ __('Site') }}</x-table.heading>


                <x-table.heading>{{ __('Building') }}</x-table.heading>


                <x-table.heading>{{ __('Floor') }}</x-table.heading>


                <x-table.heading>{{ __('Room') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($boxes ?? [] as $box)

                    <x-table.row>

                        <x-table.cell>{{ $box->number }}</x-table.cell>


                        <x-table.cell>{{ $box->year }}</x-table.cell>


                        <x-table.cell>{{ $box->stand }}</x-table.cell>


                        <x-table.cell>{{ $box->shelf }}</x-table.cell>


                        <x-table.cell>{{ $box->volumes_count }}</x-table.cell>


                        <x-table.cell>{{ $box->room->floor->building->site->name }}</x-table.cell>


                        <x-table.cell>{{ $box->room->floor->building->name }}</x-table.cell>


                        <x-table.cell>{{ $box->room->floor->number }}</x-table.cell>


                        <x-table.cell>{{ $box->room->number }}</x-table.cell>


                        <x-table.cell>

                            <x-action-button-group>

                                @can(\App\Enums\Policy::View->value, \App\Models\Box::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.box.show', $box)"
                                        :text="__('Show')"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Box::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="pencil-square"
                                        :href="route('archiving.register.box.edit', $box)"
                                        :text="__('Edit')"
                                        :title="__('Edit the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, \App\Models\Box::class)

                                    <x-link-button
                                        class="btn-danger"
                                        icon="pencil-square"
                                        {{-- href="{{ route('authorization.permission.edit', $permission) }}" --}}
                                        :text="__('Delete')"
                                        :title="__('Delete the record')"/>

                                @endcan

                            </x-action-button-group>

                        </x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="10">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </x-container>


    {{ $boxes->links() }}

</x-page>
