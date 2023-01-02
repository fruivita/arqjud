<!--
    Botão com ícone apenas, isto é, sem texto.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';

defineProps({
    icone: { type: String, required: true },
    especie: {
        type: String,
        default: 'acao',
        validator(value) {
            return ['acao', 'alerta', 'perigo', 'padrao'].includes(value);
        },
    },
    type: {
        type: String,
        default: 'button',
        validator(value) {
            return ['button', 'submit'].includes(value);
        },
    },
});
</script>

<template>
    <button
        :class="{
            'bg-green-500 hover:bg-green-600 hover:ring-green-900 focus:bg-green-600 focus:ring-green-900':
                especie === 'padrao',
            'bg-blue-500 hover:bg-blue-600 hover:ring-blue-900 focus:bg-blue-600 focus:ring-blue-900':
                especie === 'acao',
            'bg-yellow-600 hover:bg-yellow-700 hover:ring-yellow-900 focus:bg-yellow-700 focus:ring-yellow-900':
                especie === 'alerta',
            'bg-red-500 hover:bg-red-600 hover:ring-red-900 focus:bg-red-600 focus:ring-red-900':
                especie === 'perigo',
        }"
        :type="type"
        class="transform-gpu rounded p-2 text-white outline-none transition duration-300 hover:ring-4 focus:ring-4"
        icone="person"
    >
        <Icone :nome="icone" />
    </button>
</template>
