{{--
    View livewire for individual editing of sites.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the site')">

    <x-container>

        <form wire:key="form-site" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="site-name"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="site.name"
                    wire:target="update"
                    :error="$errors->first('site.name')"
                    icon="pin-map"
                    maxlength="100"
                    :placeholder="__('Site name')"
                    required
                    :text="__('Site')"
                    :title="__('Inform the site name')"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:key="site-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="site.description"
                    wire:target="update"
                    :error="$errors->first('site.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the site')"
                    :text="__('Description')"
                    :title="__('Describes the site')"
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

        <div class="overflow-x-auto">

            <x-perpage
                wire:key="per-page"
                wire:model="per_page"
                class="mb-3"
                :error="$errors->first('per_page')"/>


            <x-table wire:key="table-buildings" wire:loading.delay.class="opacity-25">

                <x-slot name="head">

                    <x-table.heading>{{ __('Building') }}</x-table.heading>


                    <x-table.heading>{{ __('Qty of floors') }}</x-table.heading>


                    <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

                </x-slot>


                <x-slot name="body">

                    @forelse ( $buildings ?? [] as $building )

                        <x-table.row>

                            <x-table.cell>{{ $building->name }}</x-table.cell>


                            <x-table.cell>{{ $building->floors_count }}</x-table.cell>


                            <x-table.cell>

                                <x-action-button-group>

                                    @can(\App\Enums\Policy::View->value, \App\Models\Building::class)

                                        <x-link-button
                                            class="btn-do"
                                            icon="eye"
                                            :href="route('archiving.register.building.show', $building)"
                                            :text="__('Show')"
                                            :title="__('Show the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Update->value, \App\Models\Building::class)

                                        <x-link-button
                                            class="btn-do"
                                            icon="pencil-square"
                                            :href="route('archiving.register.building.edit', $building)"
                                            :text="__('Edit')"
                                            :title="__('Edit the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Delete->value, $building)

                                        <x-button
                                            wire:click="markToDelete({{ $building->id }})"
                                            wire:key="btn-delete-{{ $building->id }}"
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

                            <x-table.cell colspan="3">{{ __('No record found') }}</x-table.cell>

                        </x-table.row>

                    @endforelse

                </x-slot>

            </x-table>

        </div>

    </x-container>


    {{ $buildings->links() }}


    @can(\App\Enums\Policy::Delete->value, $deleting)

        {{-- Modal to confirm the deletion --}}
        <x-confirmation-modal
            wire:model="show_delete_modal"
            wire:key="deleting-modal-{{ $deleting->id }}"
            wire:submit.prevent="destroy"
            :question="__('Delete :attribute?', ['attribute' => $deleting->name])"/>

    @endcan

</x-page>
