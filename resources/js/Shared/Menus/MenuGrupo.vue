<!--
    Agrupamento de elementos do menu principal.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import { ref } from 'vue';

defineProps({
    texto: { type: String, required: true },
});

const exibir = ref(false);
</script>

<template>
    <div class="space-y-1">
        <button
            @click="exibir = !exibir"
            class="flex w-full items-center justify-between rounded border-primaria-700 bg-primaria-100 p-2 text-left font-extrabold outline-none transition hover:border-l-4 focus:border-l-4 dark:bg-secundaria-600"
            type="button"
            dusk="btn-menu-grupo"
        >
            <span>{{ texto }}</span>

            <Icone
                nome="caret-right"
                class="h-6 w-6 transition duration-300"
                :class="{ 'rotate-90': exibir }"
            />
        </button>

        <Transition
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            enter-active-class="transition duration-300 transform-gpu"
            leave-active-class="transition duration-200 transform-gpu"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="exibir"
                class="flex flex-col space-y-1 border-l-2 border-primaria-500 pl-3 dark:border-secundaria-500"
                dusk="slot"
            >
                <slot></slot>
            </div>
        </Transition>
    </div>
</template>
