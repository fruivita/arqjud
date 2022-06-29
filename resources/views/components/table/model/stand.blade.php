{{--
    Livewire view for listing stands.

    Props:
    - colunas: colunas da tabela que devem ser exibidas
    - deleting: item to be deleted
    - parent: parent element of the item that will eventually be created
    - stands: stands that will be displayed
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
    'colunas',
    'deleting' => null,
    'parent' => null,
    'stands',
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
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Stand::class)
        )

            <x-link-button
                class="btn-do w-full md:w-auto"
                icon="plus-circle"
                :href="route('archiving.register.stand.create', $parent->id)"
                :text="__('New stand')"
                :title="__('Create a new record')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-estante"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="estante"
                :text="__('Stand')"
                value="estante"/>


            <x-form.checkbox
                wire:key="checkbox-apelido"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="apelido"
                :text="__('Alias')"
                value="apelido"/>


            <x-form.checkbox
                wire:key="checkbox-qtd-prateleiras"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="qtd_prateleiras"
                :text="__('Qty of shelves')"
                value="qtd_prateleiras"/>


            @if ($withparents)

                <x-form.checkbox
                    wire:key="checkbox-localidade"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="localidade"
                    :text="__('Site')"
                    value="localidade"/>


                <x-form.checkbox
                    wire:key="checkbox-predio"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="predio"
                    :text="__('Building')"
                    value="predio"/>


                <x-form.checkbox
                    wire:key="checkbox-andar"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="andar"
                    :text="__('Floor')"
                    value="andar"/>


                <x-form.checkbox
                    wire:key="checkbox-sala"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="colunas"
                    name="sala"
                    :text="__('Room')"
                    value="sala"/>

            @endif


            <x-form.checkbox
                wire:key="checkbox-acoes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="colunas"
                name="acoes"
                :text="__('Actions')"
                value="acoes"/>

        </x-table.acoes-tabela>

    </x-table.topo-tabela>


    <div class="overflow-x-auto">

        <x-table wire:key="table-stands" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('number')"
                    :direction="$sorts['number'] ?? null"
                    :exibir="in_array('estante', $colunas)"
                    sortable
                >

                    {{ __('Stand') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('alias')"
                    :direction="$sorts['alias'] ?? null"
                    :exibir="in_array('apelido', $colunas)"
                    sortable
                >

                    {{ __('Alias') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('shelves_count')"
                    :direction="$sorts['shelves_count'] ?? null"
                    :exibir="in_array('qtd_prateleiras', $colunas)"
                    sortable
                >

                    {{ __('Qty of shelves') }}

                </x-table.heading>


                @if ($withparents)

                    <x-table.heading
                        wire:click="sortBy('sites.name')"
                        :direction="$sorts['sites.name'] ?? null"
                        :exibir="in_array('localidade', $colunas)"
                        sortable
                    >

                        {{ __('Site') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('buildings.name')"
                        :direction="$sorts['buildings.name'] ?? null"
                        :exibir="in_array('predio', $colunas)"
                        sortable
                    >

                        {{ __('Building') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('floors.alias')"
                        :direction="$sorts['floors.alias'] ?? null"
                        :exibir="in_array('andar', $colunas)"
                        sortable
                    >

                        {{ __('Floor') }}

                    </x-table.heading>


                    <x-table.heading
                        wire:click="sortBy('rooms.number')"
                        :direction="$sorts['rooms.number'] ?? null"
                        :exibir="in_array('sala', $colunas)"
                        sortable
                    >

                        {{ __('Room') }}

                    </x-table.heading>

                @endif


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $colunas)"
                >

                    {{ __('Actions') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($stands ?? [] as $stand)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('estante', $colunas)">{{ $stand->number }}</x-table.cell>


                        <x-table.cell :exibir="in_array('apelido', $colunas)">{{ $stand->alias }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_prateleiras', $colunas)">{{ $stand->shelves_count }}</x-table.cell>


                        @if ($withparents)

                            <x-table.cell :exibir="in_array('localidade', $colunas)">{{ $stand->site_name }}</x-table.cell>


                            <x-table.cell :exibir="in_array('predio', $colunas)">{{ $stand->building_name }}</x-table.cell>


                            <x-table.cell :exibir="in_array('andar', $colunas)">{{ $stand->floor_alias }}</x-table.cell>


                            <x-table.cell :exibir="in_array('sala', $colunas)">{{ $stand->room_number }}</x-table.cell>

                        @endif


                        <x-table.cell :exibir="in_array('acoes', $colunas)">

                            <x-action-button-group>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Stand::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.stand.edit', $stand->id)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @if(
                                    $withdeletebutton == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $stand)
                                )

                                    <x-icon-button
                                        wire:click="setToDelete({{ $stand->id }})"
                                        wire:key="btn-delete-{{ $stand->id }}"
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

                        <x-table.cell colspan="{{ count($colunas) }}">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </div>


    {{ $stands->links() }}

</div>


@if(
    isset($deleting->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, $deleting)
)

    {{-- Modal to confirm the deletion --}}
    <x-confirmation-modal
        wire:model="show_delete_modal"
        wire:key="deleting-modal-{{ $deleting->id }}"
        wire:submit.prevent="destroy"
        :question="__('Delete stand :attribute?', ['attribute' => $deleting->number])"/>

@endif
