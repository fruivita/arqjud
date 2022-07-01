{{--
    Livewire view for listing permissions.

    Props:
    - limit: quantidade limite de itens filhos utilizada
    - permissions: coleção de perfis da aplicação que serão exibidos
    - preferencias: array de preferencias do usuário
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
    'permissions',
    'preferencias',
    'sorts' => [],
])


<div class="space-y-3">

    <x-table.topo-tabela>

        <div></div>


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-permissao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="permissao"
                :text="__('Permission')"
                value="permissao"/>


            <x-form.checkbox
                wire:key="checkbox-perfis"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="perfis"
                :text="__('Roles')"
                value="perfis"/>


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

        <x-table wire:key="table-permissions" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('name')"
                    :direction="$sorts['name'] ?? null"
                    :exibir="in_array('permissao', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Permission') }}

                </x-table.heading>


                <x-table.heading :exibir="in_array('perfis', $preferencias['colunas'])">

                    {{ __('Roles') }}

                </x-table.heading>


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Actions') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($permissions ?? [] as $permission)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('permissao', $preferencias['colunas'])">{{ $permission->name }}</x-table.cell>


                        <x-table.cell :exibir="in_array('perfis', $preferencias['colunas'])">

                            <ul class="divide-y divide-primary-200 dark:divide-secondary-600">

                                @forelse ($permissions->roles ?? [] as $role)

                                    <li>{{ $role->name }}</li>


                                    @if ($loop->last && $permission->roles->count() == $limit)

                                        <li class="font-bold text-right">{{ __('There may be more') }}</li>

                                    @endif

                                @empty

                                    <li>{{ __('No record found') }}</li>

                                @endforelse

                            </ul>

                        </x-table.cell>


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-action-button-group>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Permission::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('authorization.permission.edit', $permission)"
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


    <x-links-paginacao :itens="$permissions"/>

</div>
