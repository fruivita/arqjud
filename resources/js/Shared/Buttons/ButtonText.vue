<!--
    Button com texto e ícone (opcional).

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';

defineProps({
    icone: { type: String },
    especie: {
        type: String,
        default: 'padrao',
        validator(value) {
            return ['padrao', 'acao', 'inacao', 'alerta', 'perigo'].includes(value);
        },
    },
    texto: { type: String, required: true },
    type: {
        type: String,
        default: 'button',
        validator(value) {
            return ['button', 'submit'].includes(value);
        },
    },
});

const status = useStatusRequisicaoStore();
</script>

<template>
    <button
        :class="{
            'bg-primaria-500 text-primaria-50 ring-primaria-600 hover:bg-primaria-600 focus:bg-primaria-600':
                especie === 'padrao',
            'bg-blue-500 text-blue-50 ring-blue-600 hover:bg-blue-600  focus:bg-blue-600':
                especie === 'acao',
            'bg-gray-500 text-gray-50 ring-gray-600 hover:bg-gray-600 focus:bg-gray-600':
                especie === 'inacao',
            'bg-yellow-600 text-yellow-50 ring-yellow-700 hover:bg-yellow-700 focus:bg-yellow-700':
                especie === 'alerta',
            'bg-red-500 text-red-50 ring-red-600 hover:bg-red-600 focus:bg-red-600':
                especie === 'perigo',
        }"
        :disabled="status.processando"
        :type="type"
        class="inline-flex transform-gpu items-center justify-center space-x-4 rounded-lg px-4 py-2 outline-none transition duration-300 hover:ring-4 focus:ring-4 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <span dusk="texto">{{ texto }}</span>

        <Icone v-if="icone" :nome="icone" />
    </button>
</template>
