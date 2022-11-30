{{-- Disponibiliza os dados estáticos produzidos pelo backend o frontend.

    @link https://laravel.com/docs/9.x/blade
    @link https://inertiajs.com/
    @link https://www.youtube.com/watch?v=IZIzcjDdPIw --}}

<script>
    window._dados = @json($dados);
</script>
