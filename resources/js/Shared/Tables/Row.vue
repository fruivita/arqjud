<!--
    Linha (tr) da tabela padrão.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<template>
    <tr class="even:bg-primaria-100 dark:even:bg-secundaria-800" dusk="slot">
        <slot></slot>
    </tr>
</template>
