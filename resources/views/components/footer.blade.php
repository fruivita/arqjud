{{--
    Rodapé da aplicação.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
    @see https://dev.to/timosville/sticky-footer-using-tailwind-css-225p
--}}


<footer class="bg-primaria-100 px-3 py-6 text-center text-sm dark:bg-secundaria-800">

    <div class="space-y-3">

        <p class="font-bold">{{ config('app.name') . ' - ' . config('app.nome_completo')}}</p>


        <div class="flex itens-center justify-center space-x-3">

            <a href="{{ $doc_link }}" class="space-x-1">

                <x-icon name="book" class="inline"/>


                <span class="hover:underline">{{ __('Documentação') }}</span>

            </a>


            <a href="#" class="space-x-1">

                <x-icon name="git" class="inline"/>


                <span class="hover:underline">{{ __('Versão :attribute', ['attribute' => config('app.versao')]) }}</span>

            </a>

        </div>

    </div>

</footer>
