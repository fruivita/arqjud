<!--
    Link inertia estilizado como um botão.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';

defineProps({
    icone: { type: String },
    href: { type: String, required: true },
    texto: { type: String, required: true },
});

const status = useStatusRequisicaoStore();
</script>

<template>
    <InertiaLink
        :class="{
            'cursor-not-allowed opacity-50': status.processando,
        }"
        :href="href"
        class="inline-flex transform-gpu items-center justify-center space-x-4 rounded-lg bg-blue-500 px-4 py-2 text-blue-50 outline-none ring-blue-600 transition duration-300 hover:bg-blue-600 hover:ring-4 focus:bg-blue-600 focus:ring-4"
    >
        <span dusk="texto">{{ texto }}</span>

        <Icone v-if="icone" :nome="icone" />
    </InertiaLink>
</template>
