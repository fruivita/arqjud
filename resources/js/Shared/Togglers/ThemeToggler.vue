<!--
    Alternador para escolha do tema (dark/light).

    Essa escolha do usuário é armazenada no browser.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
    @link https://vueuse.org/
 -->

<script setup>
import ButtonFlutuante from '@/Shared/Buttons/ButtonFlutuante.vue';
import { useDark, useToggle } from '@vueuse/core';

const dark = useDark({ storageKey: 'tema' });
const toggle = useToggle(dark);
</script>

<template>
    <ButtonFlutuante
        :icone="dark ? 'brightness-high' : 'moon-stars'"
        @click="toggle()"
        class="right-0 z-20"
    />
</template>
