{{--
    Master Page para os documentos em PDF (relatórios, guias, etc).

    @see https://laravel.com/docs/9.x/blade
    @see https://github.com/barryvdh/laravel-dompdf
    @see https://github.com/dompdf/dompdf
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <style>
        @page {
            margin: 7.27cm 1.27cm 2cm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Open Sans", sans-serif;
        }

        .header {
            /* Fixa o cabeçalho no início de cada página */
            position: fixed;
            top: -6.3cm;
            left: 0;
            right: 0;

            /* outros estilos apropriados */
            width: 100%;
            text-align: center;
            padding-top: 10px;
        }

        .header img {
            max-width: 70px;
        }

        .footer {
            /* Fixa o rodapé no fim de cada página */
            position: fixed;
            bottom: -1.27cm;
            left: 0;
            right: 0;

            /* outros estilos apropriados */
            width: 100%;
            padding: 0;
            text-align: center;
            font-size: xx-small;
        }

        .footer .page:after {
            content: counter(page);
        }

        .footer p {
            margin: 5px;
            text-align: center;
        }

        .content {
            margin-bottom: 50px;
        }

        h4,
        h5 {
            text-align: center;
            margin: 5px;
        }

        table {
            width: 100%;
            border: 1px solid rgb(85, 85, 85);
            margin: 0;
            padding: 0;
        }

        th {
            text-transform: uppercase;
            background: rgb(173, 216, 230);
        }

        table,
        th,
        td {
            border: 1px solid rgb(85, 85, 85);
            border-collapse: collapse;
            text-align: center;
        }

        th,
        td {
            padding: 4px;
        }

        tr:nth-child(even) {
            background: rgb(238, 238, 238);
        }

        p {}

        #water-mark {
            /* Fixa a posição da marca d'água */
            position: fixed;
            top: 20%;
            width: 100%;

            /* outros estilos apropriados */
            font-size: 120px;
            color: rgb(255, 204, 203);
            text-align: center;
            opacity: .6;
            transform: rotate(-45deg);
            transform-origin: 50% 50%;
            z-index: -1000;
        }
    </style>

    <title>{{ config('app.name') }}</title>
</head>

<body>

    {{-- cabeçalho --}}
    <div class="header">
        <img src="{{ resource_path('svg/brasao-republica-colorido.svg') }}" alt="Logo">

        <h4>{{ config('orgao.poder') }}</h4>

        <h4>{{ config('orgao.especialidade') }}</h4>

        <h5>{{ config('orgao.nome') }}</h5>

        <hr>

        <h4>{{ $cabecalho }}</h4>

        @unless(\App::environment('production'))
            <div id="water-mark">{{ str(__('Exemplo'))->upper() }}</div>
        @endunless
    </div>

    {{-- rodapé --}}
    <div class="footer">
        <p>{{ __('Página') }} <span class="page"></span>/^TP^</p>

        <p>
            {{ __('Documento impresso em :attribute1 por :attribute2', [
                'attribute1' => now()->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'attribute2' => auth()->user()->nome ?? auth()->user()->username,
            ]) }}
        </p>

        <p>{{ config('app.nome_completo') }} - v.{{ config('app.versao') }} - {{ __(str()->ucfirst(\App::environment())) }}</p>
    </div>

    @yield('conteudo')
</body>

</html>
