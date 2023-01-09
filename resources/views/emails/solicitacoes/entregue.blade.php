{{--
    Markdown de template do e-mail de solicitação de processos entregue.

    @see https://laravel.com/docs/9.x/blade
    @see https://laravel.com/docs/9.x/mail
--}}
<x-mail::message>
# {{ __('Guia: :attribute', ['attribute' => $detalhes->get('guia_numero')]) }}

@unless(\App::environment('production'))
<x-mail::panel>
{{ __('Email sem valor. Disparado para fins de testes e homologação.') }}
</x-mail::panel>
@endunless

<x-mail::table>
| {{ __('Recebedor') }} | {{ __('Destino') }} | {{ __('Entregue em') }} |
|:----------------------|:---------------------------------|:------------------------|
| {{ $detalhes->get('recebedor') }} | {{ $detalhes->get('destino') }} | {{ $detalhes->get('entregue_em') }} |
</x-mail::table>

<x-mail::table>
| {{ __('Processos') }} | {{ __('Qtd volumes') }} | {{ __('Solicitante') }} |
|:----------------------|:------------------------|:------------------------|
@foreach ($detalhes->get('processos') as $processo)
| {{ cnj($processo['numero']) }} | {{ $processo['qtd_volumes'] }} | {{ data_get($processo, 'solicitante.nome') ?: data_get($processo, 'solicitante.username') }} |
@endforeach
</x-mail::table>

<x-mail::button :url="$detalhes->get('url')" color="success">
{{ __('Solicitações') }}
</x-mail::button>

@if ($detalhes->get('por_guia'))
<x-mail::subcopy>
{{ __('A presente entrega será efetivada por meio de Guia de Remessa impressa.') }}
</x-mail::subcopy>
@endif
</x-mail::message>
