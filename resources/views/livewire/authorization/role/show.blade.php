{{--
    View livewire for individual role display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="$role->name">

    <x-container class="space-y-6">

        <div class="space-y-6">

            <x-show-value
                :key="__('Description')"
                :value="$role->description"/>


            <div class="overflow-x-auto">

                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    class="mb-3"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-permissions" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Permission') }}</x-table.heading>


                        <x-table.heading>{{ __('Description') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $permissions ?? [] as $permission )

                            <x-table.row>

                                <x-table.cell>{{ $permission->name }}</x-table.cell>


                                <x-table.cell>{{ $permission->description }}</x-table.cell>

                            </x-table.row>

                        @empty

                            <x-table.row>

                                <x-table.cell colspan="2">{{ __('No record found') }}</x-table.cell>

                            </x-table.row>

                        @endforelse

                    </x-slot>

                </x-table>

            </div>


            <x-button-group>

                <x-link-button
                    class="btn-do"
                    icon="award"
                    :href="route('authorization.role.index')"
                    :text="__('Roles')"
                    :title="__('Show all records')"/>

            </x-button-group>

        </div>

    </x-container>


    {{ $permissions->links() }}

</x-page>
