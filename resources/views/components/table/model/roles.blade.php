{{--
    Livewire view for listing roles.

    Props:
    - limit: quantidade limite de itens filhos utilizada
    - preferencias: array de preferencias do usuário
    - roles: coleção de perfis da aplicação que serão exibidos
    - sorts: columns and directions used to sort

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'limit',
    'preferencias',
    'roles',
    'sorts' => [],
])


<div class="space-y-3">

    <x-table.topo-tabela>

        <div></div>


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-perfil"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="perfil"
                :text="__('Role')"
                value="perfil"/>


            <x-form.checkbox
                wire:key="checkbox-permissoes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="permissoes"
                :text="__('Permissions')"
                value="permissoes"/>


            <x-form.checkbox
                wire:key="checkbox-acoes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="acoes"
                :text="__('Actions')"
                value="acoes"/>

        </x-table.acoes-tabela>

    </x-table.topo-tabela>


    <div class="overflow-x-auto">

        <x-table wire:key="table-roles" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('name')"
                    :direction="$sorts['name'] ?? null"
                    :exibir="in_array('perfil', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Role') }}

                </x-table.heading>


                <x-table.heading :exibir="in_array('permissoes', $preferencias['colunas'])">

                    {{ __('Permissions') }}

                </x-table.heading>


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Actions') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($roles ?? [] as $role)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('perfil', $preferencias['colunas'])">{{ $role->name }}</x-table.cell>


                        <x-table.cell :exibir="in_array('permissoes', $preferencias['colunas'])">

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


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-action-button-group>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Role::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('authorization.role.edit', $role)"
                                        :title="__('Show the record')"/>

                                @endcan

                            </x-action-button-group>

                        </x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="{{ count($preferencias['colunas']) }}">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </div>


    <x-links-paginacao :itens="$roles"/>

</div>
