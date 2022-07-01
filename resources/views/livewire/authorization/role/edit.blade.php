{{--
    View livewire for individual editing of role.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the role')">

    <x-container>

        <div class="space-y-6">

            <x-form.input
                wire:key="role-name"
                wire:model.defer="role.name"
                autofocus
                :editavel="$this->modo_edicao"
                :error="$errors->first('role.name')"
                icon="award"
                maxlength="50"
                :placeholder="__('Role name')"
                required
                :text="__('Name')"
                :title="__('Role name')"
                type="text"
                withcounter/>


            <x-form.textarea
                wire:key="role-description"
                wire:model.defer="role.description"
                :editavel="$this->modo_edicao"
                :error="$errors->first('role.description')"
                icon="blockquote-left"
                maxlength="255"
                :placeholder="__('About the role')"
                :text="__('Description')"
                :title="__('Describes the role')"
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
                            wire:key="checkbox-permissao"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="preferencias.colunas"
                            editavel
                            name="permissao"
                            :text="__('Permission')"
                            value="permissao"/>


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

                    <x-table wire:key="table-permissions" wire:loading.delay.class="opacity-25">

                        <x-slot name="head">


                            <x-table.heading class="text-left" :exibir="in_array('seletores', $preferencias['colunas'])">

                                @if ($this->modo_edicao)

                                    <x-table.checkbox-action
                                        wire:key="checkbox-action"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        wire:target="per_page,update"
                                        wire:model="checkbox_action"/>

                                @endif

                            </x-table.heading>


                            <x-table.heading
                                wire:click="sortBy('name')"
                                :direction="$sorts['name'] ?? null"
                                :exibir="in_array('permissao', $preferencias['colunas'])"
                                sortable
                            >

                                {{ __('Permission') }}

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

                                            {{ __(':attribute records selected from :total', ['attribute' => is_array($this->selected) ? count($this->selected) : 0, 'total' => $this->permissions->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $this->permissions ?? [] as $permission )

                                <x-table.row wire:key="row-{{ $permission->id }}">

                                    <x-table.cell :exibir="in_array('seletores', $preferencias['colunas'])">

                                        <x-form.checkbox
                                            wire:key="checkbox-permission-{{ $permission->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            wire:model="selected"
                                            :checked="$this->role->permissions->contains($permission->id)"
                                            :editavel="$this->modo_edicao"
                                            :value="$permission->id"/>

                                    </x-table.cell>


                                    <x-table.cell :exibir="in_array('permissao', $preferencias['colunas'])">{{ $permission->name }}</x-table.cell>


                                    <x-table.cell :exibir="in_array('descricao', $preferencias['colunas'])">{{ $permission->description }}</x-table.cell>


                                    <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                                        <x-action-button-group>

                                            @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Permission::class)

                                                <x-icon-link-button
                                                    class="btn-do"
                                                    icon="eye"
                                                    :href="route('authorization.permission.edit', $permission->id)"
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


                @can(\App\Enums\Policy::Update->value, \App\Models\Role::class)

                    <x-button-group>

                        <x-form.edit-save-cancel :modo_edicao="$this->modo_edicao"/>

                    </x-button-group>

                @endcan

            </div>

        </div>

    </x-container>


    {{ $this->permissions->links() }}

</x-page>
