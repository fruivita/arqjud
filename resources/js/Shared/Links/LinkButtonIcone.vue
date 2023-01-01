<!--
    Link comum estilizado como um botão com ícone apenas, isto é, sem texto.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';

defineProps({
    icone: { type: String, required: true },
    href: { type: String, required: true },
});

const status = useStatusRequisicaoStore();
</script>

<template>
    <a
        :class="{
            'pointer-events-none opacity-50': status.processando,
        }"
        :href="href"
        class="transform-gpu rounded bg-blue-500 p-2 text-white outline-none transition duration-300 hover:bg-blue-600 hover:ring-4 hover:ring-blue-900 focus:bg-blue-600 focus:ring-4 focus:ring-blue-900"
    >
        <Icone v-if="icone" :nome="icone" />
    </a>
</template>
