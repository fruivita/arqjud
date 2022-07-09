{{--
    Feedback ao usuário para ser adicionado próximo ao elemento acionador da
    ação.

    Não é elemento intrusivo como o feedback 'notificação', visto que é exibido
    de modo inline e próximo ao elemento por alguns segundos apenas para depois
    desaparecer sem se sobrepor ao conteúdo da página.

    Aguarda um evento do tipo 'flash' acompanhado de:
    - tipo de feedback (erro ou sucesso)
    - mensagem

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<span
    x-data="{ exibir_feedback_flash : false , tipo : '', mensagem : '' }"
    x-init="
        @this.on('flash', ( t, m ) => {
            setTimeout(() => {
                exibir_feedback_flash = false;
            }, 2500);
            exibir_feedback_flash = true;
            tipo = t;
            mensagem = m;
        })
    "
    x-show="exibir_feedback_flash"
    x-text="mensagem"
    x-transition.duration.500ms
    x-bind:class="(tipo == 'sucesso') ? 'text-green-500' : 'text-red-500'"
    class="cursor-default font-bold text-center"
></span>
