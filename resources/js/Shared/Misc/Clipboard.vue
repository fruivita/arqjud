<!--
    Componente para cópia de valores para o clipboard.

    Útil para que o usuário possa clicar no botão do componente e seu conteúdo
    ser transferido para a memória, ou seja, um ctrl + c automatizado.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import Hint from '@/Shared/Misc/Hint.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useClipboard } from '@vueuse/core';

const props = defineProps({
    copiavel: { type: String },
});

const __ = useTranslationsStore().__;

const { copy, copied, isSupported } = useClipboard({
    source: props.copiavel,
});
</script>

<template>
    <Hint :texto="copied ? __('Copiado') : __('Copiar')">
        <button
            v-if="isSupported"
            :disabled="copied"
            @click="copy()"
            class="rounded p-1 opacity-50 ring-primaria-500 transition duration-150 hover:opacity-100 dark:ring-secundaria-500"
        >
            <Icone :nome="copied ? 'check-circle' : 'clipboard'" class="h-4 w-4" />
        </button>
    </Hint>
</template>
