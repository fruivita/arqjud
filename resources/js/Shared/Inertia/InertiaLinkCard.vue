<!--
    Link inertia estilizado como um card com ícone e texto.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';

defineProps({
    icone: { type: String, required: true },
    texto: { type: String, required: true },
    href: { type: String, required: true },
});

const status = useStatusRequisicaoStore();
</script>

<template>
    <div
        class="rounded bg-primaria-300 shadow-lg shadow-secundaria-500 transition duration-300 hover:bg-primaria-200 focus:bg-primaria-200 dark:bg-secundaria-600 dark:shadow-primaria-500 hover:dark:bg-secundaria-500 focus:dark:bg-secundaria-500"
    >
        <InertiaLink
            :class="{
                'cursor-not-allowed opacity-50': status.processando,
            }"
            :href="href"
            class="flex flex-col items-center space-y-6 p-3"
        >
            <Icone :nome="icone" class="h-16 w-16" />

            <span class="break-words text-center">{{ texto }}</span>
        </InertiaLink>
    </div>
</template>
