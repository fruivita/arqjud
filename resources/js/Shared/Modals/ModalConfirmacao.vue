<!--
    Modal de confirmação da operação.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import ModalBase from '@/Shared/Modals/ModalBase.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';

const __ = useTranslationsStore().__;
</script>

<template>
    <ModalBase>
        <template #header>
            <slot name="header">{{ __('Cuidado!! Operação Irreversível') }}</slot>
        </template>

        <template #body>
            <slot name="body">{{
                __('Essa operação é irreversível. Tem certeza que deseja continuar?')
            }}</slot>
        </template>

        <template #footer>
            <slot name="footer"></slot>
        </template>
    </ModalBase>
</template>
