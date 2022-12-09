<!--
    Componente destinado a exibição do último registro cadastrado.

    Útil para exibir na tela de cadastro, o último item cadastrado.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { useTranslationsStore } from '@/Stores/TranslationsStore';

defineProps({
    href: { type: String },
    texto: { type: String },
});

const __ = useTranslationsStore().__;
</script>

<template>
    <div>
        <div class="text-sm text-primaria-700 dark:text-secundaria-300">
            {{ __('Último item cadastrado:') }}
        </div>

        <component
            :class="{
                'underline hover:opacity-80': href,
            }"
            :href="href"
            :is="href ? 'InertiaLink' : 'p'"
            class="font-bold"
            v-html="texto ?? __('Nenhum item cadastrado até o momento')"
        />
    </div>
</template>
