<!--
    Contador de caracteres para os elementos de formulário.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { computed } from 'vue';

const props = defineProps({
    texto: { type: String, required: true },
    maxlength: { type: [Number, String], required: true },
});

const contador = computed(() => props.texto?.length ?? 0);
</script>

<template>
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm">
        <span dusk="contador">{{ contador }}</span
        >/<span dusk="maxlength">{{ maxlength }}</span>
    </div>
</template>
