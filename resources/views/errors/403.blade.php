{{--
    View para erro HTTP 403.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/9.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends ('layouts.error')


@section ('titulo', __('error.403.titulo'))
@section ('codigo', '403')
@section ('mensagem', __('error.403.mensagem'))
