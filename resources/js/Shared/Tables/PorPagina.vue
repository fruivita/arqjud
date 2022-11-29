<!--
    Seletor de paginação dos resultados.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
    @link https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { perPageKey, updatePerPageKey } from '@/keys.js';
import { useDadosEstaticosStore } from '@/Stores/DadosEstaticosStore';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { computed, inject } from 'vue';

const __ = useTranslationsStore().__;
const opcoes = useDadosEstaticosStore().paginacao;

const perPage = inject(perPageKey);
const updatePerPage = inject(updatePerPageKey);

const opcaoEscolhida = computed({
    get: () => perPage.value,
    set: updatePerPage,
});
</script>

<template>
    <div class="space-x-3 text-right">
        <label for="por_pagina">{{ __('Paginação') }}</label>

        <select
            id="por_pagina"
            v-model="opcaoEscolhida"
            class="rounded bg-primaria-300 text-right dark:bg-secundaria-500"
        >
            <option v-for="opcao in opcoes" :key="opcao" :value="opcao">
                {{ opcao }}
            </option>
        </select>
    </div>
</template>
