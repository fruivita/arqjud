<!--
    Link do menu principal.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';

defineProps({
    ativo: { type: Boolean, default: false },
    icone: { type: String },
    texto: { type: String, required: true },
});
</script>

<template>
    <InertiaLink
        :class="{
            'border-l-4 bg-primaria-400 text-primaria-50 dark:bg-secundaria-800': ativo,
            'hover:border-l-4 focus:border-l-4': !ativo,
        }"
        class="flex items-center space-x-3 rounded border-primaria-700 py-1 pl-3 font-bold outline-none"
    >
        <Icone v-if="icone" :nome="icone" />

        <span dusk="texto">{{ texto }}</span>
    </InertiaLink>
</template>
