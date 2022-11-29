<!--
    Container para as mensagens de alerta.
    Não se trata de notificação, mas de alerta exibido no corpo da página.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->
<template>
    <div class="rounded bg-red-300 p-3 font-mono text-red-900">
        <slot></slot>
    </div>
</template>
