{{--
    Elemento não clicável que imita um link do menu. Estilizado dessa maneira
    por questões puramente estéticas.

    Props:
    - icone: string com o nome do ícone para ser utilizado
    - texto: string para exibição no componente

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['icone', 'texto'])


<li>

    <div class="flex items-center pl-3 space-x-3">

        <x-icon :name="$icone"/>


        <span>{{ $texto }}</span>

    </div>

</li>
