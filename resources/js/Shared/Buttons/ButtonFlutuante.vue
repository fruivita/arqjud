<!--
    Botão flutuante, isto é, fora do fluxo da página.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';

defineProps({
    icone: { type: String, required: true },
});
</script>

<template>
    <button
        class="fixed transform-gpu rounded bg-primaria-200 p-3 opacity-50 outline-none ring-4 ring-inset ring-primaria-500 transition duration-300 hover:opacity-100 focus:opacity-100 dark:bg-secundaria-900 dark:ring-secundaria-500"
    >
        <Transition
            enter-from-class="opacity-0 -translate-y-5"
            enter-to-class="opacity-100 translate-y-0"
            enter-active-class="transition duration-300 transform-gpu"
        >
            <Icone :nome="icone" />
        </Transition>
    </button>
</template>
