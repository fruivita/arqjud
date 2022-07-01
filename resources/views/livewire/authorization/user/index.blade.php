{{--
    View livewire for and update users' role.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Users and role')">

    <x-search
        wire:key="search"
        wire:model.debounce.500ms="term"
        :error="$errors->first('term')"
        withcounter/>


    <x-container>

        <div class="space-y-3">

            <x-table.topo-tabela>

                <div></div>


                <x-table.acoes-tabela>

                    <x-form.checkbox
                        wire:key="checkbox-nome"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="preferencias.colunas"
                        editavel
                        name="nome"
                        :text="__('Name')"
                        value="nome"/>


                    <x-form.checkbox
                        wire:key="checkbox-usuario"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="preferencias.colunas"
                        editavel
                        name="usuario"
                        :text="__('Username')"
                        value="usuario"/>


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
                        wire:key="checkbox-delegante"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="preferencias.colunas"
                        editavel
                        name="delegante"
                        :text="__('Delegator')"
                        value="delegante"/>


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

                <x-table wire:key="table-users" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading
                            wire:click="sortBy('name')"
                            :direction="$sorts['name'] ?? null"
                            :exibir="in_array('nome', $preferencias['colunas'])"
                            sortable
                        >

                            {{ __('Name') }}

                        </x-table.heading>


                        <x-table.heading
                            wire:click="sortBy('username')"
                            :direction="$sorts['username'] ?? null"
                            :exibir="in_array('usuario', $preferencias['colunas'])"
                            sortable
                        >

                            {{ __('Username') }}

                        </x-table.heading>


                        <x-table.heading :exibir="in_array('perfil', $preferencias['colunas'])">{{ __('Role') }}</x-table.heading>


                        <x-table.heading :exibir="in_array('delegante', $preferencias['colunas'])">{{ __('Delegator') }}</x-table.heading>


                        <x-table.heading
                            class="w-10"
                            :exibir="in_array('acoes', $preferencias['colunas'])"
                        >

                            {{ __('Actions') }}

                        </x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ($this->users ?? [] as $user)

                            <x-table.row>

                                <x-table.cell :exibir="in_array('nome', $preferencias['colunas'])">{{ $user->name }}</x-table.cell>


                                <x-table.cell :exibir="in_array('usuario', $preferencias['colunas'])">{{ $user->username }}</x-table.cell>


                                <x-table.cell :exibir="in_array('perfil', $preferencias['colunas'])">{{ $user->role->name }}</x-table.cell>


                                <x-table.cell :exibir="in_array('delegante', $preferencias['colunas'])">{{ optional($user->delegator)->username }}</x-table.cell>


                                <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                                    <x-action-button-group>

                                        @can(\App\Enums\Policy::Update->value, \App\Models\User::class)

                                            <x-icon-button
                                                wire:click="edit({{ $user->id }})"
                                                wire:key="edit-button-{{ $user->id }}"
                                                wire:loading.delay.attr="disabled"
                                                wire:loading.delay.class="cursor-not-allowed"
                                                class="btn-do"
                                                icon="pencil-square"
                                                :title="__('Edit the record')"
                                                type="button"/>

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


            {{ $this->users->links() }}

        </div>

    </x-container>


    @can(\App\Enums\Policy::Update->value, \App\Models\User::class)

        <form
            wire:key="editing-user-form-{{ $this->editing->id }}"
            wire:submit.prevent="update"
            method="POST"
        >

            <x-modal
                wire:key="editing-user-modal-{{ $this->editing->id }}"
                wire:model="show_edit_modal"
            >

                <x-slot name="title">{{ $this->editing->username . ' ' . $this->editing->name }}</x-slot>


                <x-slot name="content">

                    <div wire:key="wrapper-edit-user-{{ $this->editing->id }}" wire:loading.delay.class="opacity-25">

                        <x-form.select
                            wire:key="editing-user-{{ $this->editing->id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="editing.role_id"
                            editavel
                            :error="$errors->first('editing.role_id')"
                            icon="award"
                            required
                            :text="__('Role')"
                            :title="__('Choose role')">

                            @foreach ($this->roles ?? [] as $role)

                                <option value="{{ $role->id }}">

                                    {{ $role->name }}

                                </option>

                            @endforeach

                        </x-form.select>

                    </div>

                </x-slot>


                <x-slot name="footer">

                    <x-feedback.inline/>


                    <x-button
                        class="btn-do"
                        icon="save"
                        :text="__('Save')"
                        :title="__('Save the record')"
                        type="submit"/>

                </x-slot>

            </x-modal>

        </form>

    @endcan

</x-page>
