{{--
    View livewire for listing the floors.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Floors')">

    <x-container>

        <div class="flex items-center justify-between mb-3">

            @can(\App\Enums\Policy::Create->value, \App\Models\Floor::class)

                <x-link-button
                    class="btn-do"
                    icon="plus-circle"
                    {{-- href="{{ route('administration.doc.create') }}" --}}
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


        <x-table wire:key="table-floor" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Floor') }}</x-table.heading>


                <x-table.heading>{{ __('Rooms') }}</x-table.heading>


                <x-table.heading>{{ __('Site') }}</x-table.heading>


                <x-table.heading>{{ __('Building') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($floors ?? [] as $floor)

                    <x-table.row>

                        <x-table.cell>{{ $floor->number }}</x-table.cell>


                        <x-table.cell>{{ $floor->rooms_count }}</x-table.cell>


                        <x-table.cell>{{ $floor->building->site->name }}</x-table.cell>


                        <x-table.cell>{{ $floor->building->name }}</x-table.cell>


                        <x-table.cell>

                            <div class="flex flex-col justify-center space-y-3">

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
                                        {{-- href="{{ route('administration.doc.edit', $doc) }}" --}}
                                        :text="__('Edit')"
                                        :title="__('Edit the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, \App\Models\Floor::class)

                                    <x-button
                                        wire:click="markToDelete({{ $floor->id }})"
                                        wire:key="btn-delete-{{ $floor->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger w-full"
                                        icon="trash"
                                        :text="__('Delete')"
                                        :title="__('Delete the record')"
                                        type="button"/>

                                @endcan

                            </div>

                        </x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="5">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </x-container>


    {{ $floors->links() }}


    @can(\App\Enums\Policy::Delete->value, \App\Models\Floor::class)

        {{-- Modal to confirm the deletion --}}
        <x-confirmation-modal
            wire:model="show_delete_modal"
            wire:key="deleting-modal-{{ $deleting->id }}"
            wire:submit.prevent="destroy"
            :question="__('Delete :attribute?', ['attribute' => $deleting->number])"/>

    @endcan

</x-page>
