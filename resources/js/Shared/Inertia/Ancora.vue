<!--
    Link inertia estilizado para, sem chamar muita atenção, indicar determinado
    link. Indicado para ser inserido ao lado de um texto.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';

defineProps({
    href: { type: String, required: true },
});
</script>

<template>
    <InertiaLink
        :href="href"
        class="rounded p-1 opacity-50 ring-primaria-500 transition duration-150 hover:opacity-100 dark:ring-secundaria-500"
    >
        <Icone nome="link-45deg" class="h-5 w-5" />
    </InertiaLink>
</template>
