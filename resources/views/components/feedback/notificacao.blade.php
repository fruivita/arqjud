{{--
    Feedback na forma de uma notificação popup para responder a uma requisição
    do usuário.

    Por se tratar de elemento invasivo, deve ser utilizado apenas quando o
    feedback do tipo flash não puder ser utilizado.

    Após certo período de tempo, a notificação desaparace automaticamente.

    Porém, caso o usuário passe o mouse sobre a notificação (mouse over), o
    desaparecimento automático será cancelado.

    O componente aguarda por um evento chamado 'notificacao' acompanhado de:
    - tipo de feedback (erro ou sucesso)
    - ícone representativo
    - cabeçalho
    - mensagem
    - duração da exibição

    Noto: Como se trata de elemento intrusivo, ele se sobrepõe ao conteúdo em
    exibição.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}

<div
    x-data="{
        notificacoes : [],
        remove(notificacao) {
            this.notificacoes.splice(this.notificacoes.indexOf(notificacao), 1)
        },
    }"
    x-on:notificacao.window="
        notificacao = $event.detail;
        notificacao.duracao = $event.detail.duracao ?? 2500;
        notificacao.duracao_id = setTimeout(() => {
            remove(notificacao)
        }, notificacao.duracao);
        notificacoes.push(notificacao);
    "
    x-transition.duration.500ms
    class="top-12 fixed right-3 z-30 space-y-3"
>

    <template x-for="(notificacao, notificacaoIndex) in notificacoes" :key="notificacaoIndex">

        <div
            x-bind:class="notificacao.tipo"
            x-on:mouseover.once="clearTimeout(notificacao.duracao_id)"
            class="border-l-8 border-r-8 p-3"
        >

            <div class="flex items-center space-x-3">

                {{-- ícone de contexto --}}
                <div x-html="notificacao.icone"></div>


                <div x-bind:class="(notificacao.cabecalho && notificacao.mensagem) ? 'space-y-3' : ''">

                    {{-- cabeçalho --}}
                    <h3 x-text="notificacao.cabecalho" class="font-bold text-lg"></h3>


                    {{-- mensagem propriamente dita --}}
                    <p class="text-center" x-text="notificacao.mensagem"></p>

                </div>

                    {{-- botão para fechar a caixa de mensagem --}}
                    <button x-on:click="remove(notificacao)" class="animate-none lg:animate-ping" id="btn-flash" type="button">

                        <x-icon name="x-circle"/>

                    </button>

                </div>

            </div>

        </div>

    </template>

</div>
