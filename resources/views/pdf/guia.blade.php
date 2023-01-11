{{--
    Subview para exibir a guia em formato PDF.

    @see https://laravel.com/docs/9.x/blade
    @see https://github.com/barryvdh/laravel-dompdf
    @see https://github.com/dompdf/dompdf
--}}

@extends('layouts.pdf')

@section('conteudo')
    <div class="content">
        <p>{{ __('Número: :attribute', ['attribute' => $guia->paraHumano]) }}</p>

        <p>{{ __('Remetente: :attribute', ['attribute' => $guia->remetente['nome']]) }}</p>

        <p>{{ __('Destino: :attribute1 - :attribute2', [ 'attribute1' => str($guia->destino['sigla'])->upper(), 'attribute2' => $guia->destino['nome']]) }}</p>

        <table style="table-layout:fixed;">
            <thead>
                <tr>
                    <th>{{ __('Processos') }}</th>

                    <th>{{ __('Volumes') }}</th>

                    <th>{{ __('Solicitante') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($guia->processos ?? [] as $processo)
                    <tr>
                        <td>{{ cnj($processo['numero']) }}</td>

                        <td>{{ $processo['qtd_volumes'] }}</td>

                        <td>{{ data_get($processo, 'solicitante.nome') ?: data_get($processo, 'solicitante.matricula')  }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">{{ __('Nenhum registro encontrado!') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>


        <div style="margin-top: 2cm">
            <hr>

            <p style="text-align: center">
                {{ __('Recebedor(a): :attribute', ['attribute' => $guia->recebedor['nome']]) }}
            </p>

            <p style="text-align: center">
                {{ dataCompleta($guia->gerada_em->tz(config('app.tz'))) }}
            </p>
        </div>
    </div>
@endsection
