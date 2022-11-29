<!--
    Mensagem de erro de validação para os elementos de formulário.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
defineProps({
    erro: { type: String, required: true },
});
</script>

<template>
    <p class="text-sm font-bold text-red-500" dusk="erro">{{ erro }}</p>
</template>
