{{--
    View livewire for listing the roles.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Roles and permissions')">

    <x-container>

        <x-perpage
            wire:key="per-page"
            wire:model="per_page"
            class="mb-3"
            :error="$errors->first('per_page')"/>


        <x-table wire:key="table-roles-permission" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Role') }}</x-table.heading>


                <x-table.heading>{{ __('Permissions') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($roles ?? [] as $role)

                    <x-table.row>

                        <x-table.cell>{{ $role->name }}</x-table.cell>


                        <x-table.cell>

                            <ul class="divide-y divide-primary-200 dark:divide-secondary-600">

                                @forelse ($role->permissions ?? [] as $permission)

                                    <li>{{ $permission->name }}</li>


                                    @if ($loop->last && $role->permissions->count() == $limit)

                                        <li class="font-bold text-right">{{ __('There may be more') }}</li>

                                    @endif

                                @empty

                                    <li>{{ __('No record found') }}</li>

                                @endforelse

                            </ul>

                        </x-table.cell>


                        <x-table.cell>

                            <x-action-button-group>

                                @can(\App\Enums\Policy::View->value, \App\Models\Role::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('authorization.role.show', $role)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Role::class)

                                    <x-icon-link-button
                                        class="btn-do-alterative"
                                        icon="pencil-square"
                                        :href="route('authorization.role.edit', $role)"
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

    </x-container>


    {{ $roles->links() }}

</x-page>
