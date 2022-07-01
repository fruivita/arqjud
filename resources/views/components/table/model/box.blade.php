{{--
    Livewire view for listing boxes.

    Props:
    - boxes: boxes that will be displayed
    - deleting: item to be deleted
    - parent: parent element of the item that will eventually be created
    - preferencias: array de preferencias do usuário
    - sorts: columns and directions used to sort
    - withdeletebutton: whether the delete button should be displayed
    - withnewbutton: whether the new button should be displayed
    - withparents: whether the parent info should be displayed

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'boxes',
    'deleting' => null,
    'parent' => null,
    'preferencias',
    'sorts' => [],
    'withdeletebutton' => false,
    'withnewbutton' => false,
    'withparents' => false
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $withnewbutton == true
            && isset($parent)
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Box::class)
        )

            <x-link-button
                class="btn-do w-full md:w-auto"
                icon="plus-circle"
                :href="route('archiving.register.box.create', $parent->id)"
                :text="__('New box')"
                :title="__('Create a new record')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-caixa"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="caixa"
                :text="__('Box')"
                value="caixa"/>


            <x-form.checkbox
                wire:key="checkbox-ano"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="ano"
                :text="__('Year')"
                value="ano"/>


            <x-form.checkbox
                wire:key="checkbox-qtd-volumes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="qtd_volumes"
                :text="__('Qty of volumes')"
                value="qtd_volumes"/>


            @if ($withparents)

                <x-form.checkbox
                    wire:key="checkbox-localidade"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="localidade"
                    :text="__('Site')"
                    value="localidade"/>


                <x-form.checkbox
                    wire:key="checkbox-predio"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="predio"
                    :text="__('Building')"
                    value="predio"/>


                <x-form.checkbox
                    wire:key="checkbox-andar"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="andar"
                    :text="__('Floor')"
                    value="andar"/>


                <x-form.checkbox
                    wire:key="checkbox-sala"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="sala"
                    :text="__('Room')"
                    value="sala"/>


                <x-form.checkbox
                    wire:key="checkbox-estante"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="estante"
                    :text="__('Stand')"
                    value="estante"/>


                <x-form.checkbox
                    wire:key="checkbox-prateleira"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="prateleira"
                    :text="__('Shelf')"
                    value="prateleira"/>

            @endif


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

        <x-table wire:key="table-boxes" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('number')"
                    :direction="$sorts['number'] ?? null"
                    :exibir="in_array('caixa', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Box') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('year')"
                    :direction="$sorts['year'] ?? null"
                    :exibir="in_array('ano', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Year') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('volumes_count')"
                    :direction="$sorts['volumes_count'] ?? null"
                    :exibir="in_array('qtd_volumes', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Qty of volumes') }}

                </x-table.heading>


                @if ($withparents)

                    <x-table.heading
                        wire:click="sortBy('sites.name')"
                        :direction="$sorts['sites.name'] ?? null"
                        :exibir="in_array('localidade', $preferencias['colunas'])"
                        sortable
                    >

                        {{ __('Site') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('buildings.name')"
                        :direction="$sorts['buildings.name'] ?? null"
                        :exibir="in_array('predio', $preferencias['colunas'])"
                        sortable
                    >

                        {{ __('Building') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('floors.alias')"
                        :direction="$sorts['floors.alias'] ?? null"
                        :exibir="in_array('andar', $preferencias['colunas'])"
                        sortable
                    >

                        {{ __('Floor') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('rooms.number')"
                        :direction="$sorts['rooms.number'] ?? null"
                        :exibir="in_array('sala', $preferencias['colunas'])"
                        sortable
                    >

                        {{ __('Room') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('stands.number')"
                        :direction="$sorts['stands.number'] ?? null"
                        :exibir="in_array('estante', $preferencias['colunas'])"
                        sortable
                    >

                        {{ __('Stand') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('shelves.number')"
                        :direction="$sorts['shelves.number'] ?? null"
                        :exibir="in_array('prateleira', $preferencias['colunas'])"
                        sortable
                    >

                        {{ __('Shelf') }}

                    </x-table.heading>

                @endif


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Actions') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($boxes ?? [] as $box)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('caixa', $preferencias['colunas'])">{{ $box->number }}</x-table.cell>


                        <x-table.cell :exibir="in_array('ano', $preferencias['colunas'])">{{ $box->year }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_volumes', $preferencias['colunas'])">{{ $box->volumes_count }}</x-table.cell>


                        @if ($withparents)

                            <x-table.cell :exibir="in_array('localidade', $preferencias['colunas'])">{{ $box->site_name }}</x-table.cell>


                            <x-table.cell :exibir="in_array('predio', $preferencias['colunas'])">{{ $box->building_name }}</x-table.cell>


                            <x-table.cell :exibir="in_array('andar', $preferencias['colunas'])">{{ $box->floor_alias }}</x-table.cell>


                            <x-table.cell :exibir="in_array('sala', $preferencias['colunas'])">{{ $box->room_number }}</x-table.cell>


                            <x-table.cell :exibir="in_array('estante', $preferencias['colunas'])">{{ $box->stand_for_humans }}</x-table.cell>


                            <x-table.cell :exibir="in_array('prateleira', $preferencias['colunas'])">{{ $box->shelf_for_humans }}</x-table.cell>

                        @endif


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-action-button-group>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Box::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.box.edit', $box->id)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @if(
                                    $withdeletebutton == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $box)
                                )

                                    <x-icon-button
                                        wire:click="setToDelete({{ $box->id }})"
                                        wire:key="btn-delete-{{ $box->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger w-full"
                                        icon="trash"
                                        :title="__('Delete the record')"
                                        type="button"/>

                                @endif

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


    {{ $boxes->links() }}

</div>


@if(
    isset($deleting->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, $deleting)
)

    {{-- Modal to confirm the deletion --}}
    <x-confirmation-modal
        wire:click="destroy"
        wire:key="deleting-modal-{{ $deleting->id }}"
        wire:model="show_delete_modal"
        :question="__('Delete box :attribute?', ['attribute' => $deleting->for_humans])"/>

@endif
