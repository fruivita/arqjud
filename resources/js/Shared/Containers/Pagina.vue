<!--
    Container para o conteúdo propriamente dito da página.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
defineProps({
    titulo: { type: String },
});
</script>

<template>
    <article class="space-y-12 py-3">
        <h1 v-if="titulo" class="text-center text-2xl font-bold" dusk="titulo">
            {{ titulo }}
        </h1>

        <div class="space-y-12" dusk="slot">
            <slot></slot>
        </div>
    </article>
</template>
