<!--
    Container para partes ou seções em uma página.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<template>
    <div
        class="overflow-x-auto rounded-lg p-3 shadow-lg shadow-secundaria-500 dark:shadow-primaria-500"
        dusk="slot"
    >
        <slot></slot>
    </div>
</template>
