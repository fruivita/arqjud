<!--
    Layout padrão da aplicação destinado aos usuários autenticados.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
    @see https://vueuse.org/
 -->

<script setup>
import ButtonFlutuante from '@/Shared/Buttons/ButtonFlutuante.vue';
import TheMenu from '@/Shared/Menus/TheMenu.vue';
import TheFooter from '@/Shared/Misc/TheFooter.vue';
import ThemeToggler from '@/Shared/Togglers/ThemeToggler.vue';
import { useDadosEstaticosStore } from '@/Stores/DadosEstaticosStore';
import { Head as InertiaHead } from '@inertiajs/inertia-vue3';
import { computed } from '@vue/reactivity';
import { useLocalStorage } from '@vueuse/core';

const exibirMenuPrincipal = useLocalStorage('exibirMenuPrincipal', true);

const title = computed(
    () => `${useDadosEstaticosStore().appNome} - ${useDadosEstaticosStore().orgaoSigla}`
);
</script>

<template>
    <div
        class="bg-primaria-50 text-xl text-primaria-900 transition duration-300 dark:bg-secundaria-900 dark:text-secundaria-50"
    >
        <InertiaHead :title="title" />

        <ButtonFlutuante
            @click="exibirMenuPrincipal = !exibirMenuPrincipal"
            class="z-30"
            icone="list"
        />

        <ThemeToggler />

        <TheMenu v-show="exibirMenuPrincipal" class="z-20" />

        <div
            :class="{
                'lg:ml-80': exibirMenuPrincipal,
            }"
            class="flex min-h-screen flex-col"
        >
            <main class="flex flex-grow flex-col lg:px-6">
                <slot></slot>
            </main>

            <TheFooter />
        </div>
    </div>
</template>
