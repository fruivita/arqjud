<!--
    Container para os cards de visualização de informação.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
const props = defineProps({
    titulo: { type: String, required: true },
    texto: { type: [Number, String], required: true },
    especie: {
        type: String,
        default: 'info',
        validator(value) {
            return ['info', 'alerta', 'perigo'].includes(value);
        },
    },
});
</script>

<template>
    <div
        :class="{
            'bg-green-500 p-3 text-green-50': especie === 'info',
            'bg-yellow-500 p-3 text-yellow-50': especie === 'alerta',
            'bg-red-500 p-3 text-red-50': especie === 'perigo',
        }"
        class="flex flex-col items-center justify-center space-y-3 rounded-lg"
    >
        <h3 class="font-mono text-2xl font-bold">{{ titulo }}</h3>

        <p
            :class="{
                'bg-green-700': especie === 'info',
                'bg-yellow-700': especie === 'alerta',
                'bg-red-700': especie === 'perigo',
            }"
            class="rounded-full px-3 text-5xl"
        >
            {{ texto }}
        </p>
    </div>
</template>
