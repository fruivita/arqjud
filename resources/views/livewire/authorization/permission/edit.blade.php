{{--
    View livewire for individual editing of permissions.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the permission')">

    <x-container>

        <div class="space-y-6">

            <x-form.input
                wire:key="permission-name"
                wire:model.defer="permission.name"
                autofocus
                :editavel="$this->modo_edicao"
                :error="$errors->first('permission.name')"
                icon="vector-pen"
                maxlength="50"
                :placeholder="__('Permission name')"
                required
                :text="__('Name')"
                :title="__('Inform the permission name')"
                type="text"
                withcounter/>


            <x-form.textarea
                wire:key="permission-description"
                wire:model.defer="permission.description"
                :editavel="$this->modo_edicao"
                :error="$errors->first('permission.description')"
                icon="blockquote-left"
                maxlength="255"
                :placeholder="__('About the permission')"
                :text="__('Description')"
                :title="__('Describes the permission')"
                withcounter/>


            <div class="space-y-3">

                <x-table.topo-tabela>

                    <div></div>


                    <x-table.acoes-tabela>

                        <x-form.checkbox
                            wire:key="checkbox-seletores"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="preferencias.colunas"
                            editavel
                            name="seletores"
                            :text="__('Seletores')"
                            value="seletores"/>


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
                            wire:key="checkbox-descricao"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="preferencias.colunas"
                            editavel
                            name="descricao"
                            :text="__('Description')"
                            value="descricao"/>


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

                            <x-table.heading class="text-left" :exibir="in_array('seletores', $preferencias['colunas'])">

                                @if ($this->modo_edicao)

                                    <x-table.checkbox-action
                                        wire:key="checkbox-action"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        wire:target="update"
                                        wire:model="checkbox_action"/>


                                @endif

                            </x-table.heading>


                            <x-table.heading
                                wire:click="sortBy('name')"
                                :direction="$sorts['name'] ?? null"
                                :exibir="in_array('perfil', $preferencias['colunas'])"
                                sortable
                            >

                                {{ __('Role') }}

                            </x-table.heading>


                            <x-table.heading
                                wire:click="sortBy('description')"
                                :direction="$sorts['description'] ?? null"
                                :exibir="in_array('descricao', $preferencias['colunas'])"
                                sortable
                            >

                                {{ __('Description') }}

                            </x-table.heading>


                            <x-table.heading
                                class="w-10"
                                :exibir="in_array('acoes', $preferencias['colunas'])"
                            >

                                {{ __('Actions') }}

                            </x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            <x-table.row wire:key="row-select-counter">

                                <x-table.cell class="text-left" colspan="{{ count($preferencias['colunas']) }}">

                                    <p>

                                        <span class="font-bold">

                                            {{ __(':attribute records selected from :total', ['attribute' => is_array($this->selected) ? count($this->selected) : 0, 'total' => $this->roles->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $this->roles ?? [] as $role )

                                <x-table.row wire:key="row-{{ $role->id }}">

                                    <x-table.cell :exibir="in_array('seletores', $preferencias['colunas'])">

                                        <x-form.checkbox
                                            wire:key="checkbox-role-{{ $role->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            wire:model="selected"
                                            :checked="$this->permission->roles->contains($role->id)"
                                            :editavel="$this->modo_edicao"
                                            :value="$role->id"/>

                                    </x-table.cell>


                                    <x-table.cell :exibir="in_array('perfil', $preferencias['colunas'])">{{ $role->name }}</x-table.cell>


                                    <x-table.cell :exibir="in_array('descricao', $preferencias['colunas'])">{{ $role->description }}</x-table.cell>


                                    <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                                        <x-action-button-group>

                                            @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Role::class)

                                                <x-icon-link-button
                                                    class="btn-do"
                                                    icon="eye"
                                                    :href="route('authorization.role.edit', $role->id)"
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


                    @error('selected')

                        <x-error>{{ $message }}</x-error>

                    @enderror

                </div>


                @can(\App\Enums\Policy::Update->value, \App\Models\Permission::class)

                    <x-button-group>

                        <x-form.edit-save-cancel :modo_edicao="$this->modo_edicao"/>

                    </x-button-group>

                @endcan

            </div>

        </div>

    </x-container>


    <x-links-paginacao :itens="$this->roles"/>

</x-page>
