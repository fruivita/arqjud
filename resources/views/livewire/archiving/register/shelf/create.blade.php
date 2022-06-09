{{--
    View livewire for individual creation of shelf.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New shelves')">

    <x-backtrace :model="$stand" :root="true"/>


    <x-container>

        <form wire:key="form-shelf" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <x-show-value
                    :key="__('Site')"
                    :value="$stand->room->floor->building->site->name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$stand->room->floor->building->name"/>


                <x-show-value
                    :key="__('Floor')"
                    :value="$stand->room->floor->number"/>


                <x-show-value
                    :key="__('Room')"
                    :value="$stand->room->number"/>


                <x-show-value
                    :key="__('Stand')"
                    :value="$stand->number"/>


                <x-form.input
                    wire:key="shelf-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="shelf.number"
                    wire:target="store"
                    :error="$errors->first('shelf.number')"
                    icon="list-nested"
                    min="1"
                    max="100000"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Shelf')"
                    :title="__('Inform the shelf number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="shelf-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="shelf.description"
                    wire:target="store"
                    :error="$errors->first('shelf.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the shelf')"
                    :text="__('Description')"
                    :title="__('Describes the shelf')"
                    withcounter/>


                <x-button-group>

                    <x-feedback.inline/>


                    <x-button
                        class="btn-do"
                        icon="save"
                        :text="__('Save')"
                        :title="__('Save the record')"
                        type="submit"/>

                </x-button-group>

            </div>

        </form>

    </x-container>


    <x-container>

        <x-perpage
            wire:key="per-page"
            wire:model="per_page"
            class="mb-3"
            :error="$errors->first('per_page')"/>


        <x-table wire:key="table-shelves" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Shelf') }}</x-table.heading>


                <x-table.heading>{{ __('Qty of boxes') }}</x-table.heading>


                <x-table.heading>{{ __('Site') }}</x-table.heading>


                <x-table.heading>{{ __('Building') }}</x-table.heading>


                <x-table.heading>{{ __('Floor') }}</x-table.heading>


                <x-table.heading>{{ __('Room') }}</x-table.heading>


                <x-table.heading>{{ __('Stand') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($shelves ?? [] as $shelf)

                    <x-table.row>

                        <x-table.cell>{{ $shelf->number }}</x-table.cell>


                        <x-table.cell>{{ $shelf->boxes_count }}</x-table.cell>


                        <x-table.cell>{{ $shelf->stand->room->floor->building->site->name }}</x-table.cell>


                        <x-table.cell>{{ $shelf->stand->room->floor->building->name }}</x-table.cell>


                        <x-table.cell>{{ $shelf->stand->room->floor->number }}</x-table.cell>


                        <x-table.cell>{{ $shelf->stand->room->number }}</x-table.cell>


                        <x-table.cell>{{ $shelf->stand->number }}</x-table.cell>


                        <x-table.cell>

                            <x-action-button-group>

                                @can(\App\Enums\Policy::View->value, \App\Models\Shelf::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.shelf.show', $shelf)"
                                        :text="__('Show')"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Shelf::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="pencil-square"
                                        :href="route('archiving.register.shelf.edit', $shelf)"
                                        :text="__('Edit')"
                                        :title="__('Edit the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, $shelf)

                                    <x-button
                                        wire:click="markToDelete({{ $shelf->id }})"
                                        wire:key="btn-delete-{{ $shelf->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger w-full"
                                        icon="trash"
                                        :text="__('Delete')"
                                        :title="__('Delete the record')"
                                        type="button"/>

                                @endcan

                            </x-action-button-group>

                        </x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="8">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </x-container>


    {{ $shelves->links() }}


    @can(\App\Enums\Policy::Delete->value, $deleting)

        {{-- Modal to confirm the deletion --}}
        <x-confirmation-modal
            wire:model="show_delete_modal"
            wire:key="deleting-modal-{{ $deleting->id }}"
            wire:submit.prevent="destroy"
            :question="__('Delete :attribute?', ['attribute' => $deleting->number])"/>

    @endcan

</x-page>
