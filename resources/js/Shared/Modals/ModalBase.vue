<!--
    Modal padrão que deve ser utilizado pelos modais específicos.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
    @link https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { exibirModalKey, fecharModalKey } from '@/keys';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { onClickOutside } from '@vueuse/core';
import { inject, ref } from 'vue';

const __ = useTranslationsStore().__;

const exibirModal = inject(exibirModalKey);
const fecharModal = inject(fecharModalKey);

const container = ref(null);

onClickOutside(container, fecharModal);
</script>

<template>
    <Transition
        enter-from-class="opacity-0 scale-150"
        enter-to-class="opacity-100 scale-100"
        enter-active-class="transition duration-300 transform-gpu"
        leave-active-class="transition duration-200 transform-gpu"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-150"
    >
        <div
            v-if="exibirModal"
            class="fixed inset-0 z-20 grid place-items-center bg-primaria-100/80 text-xl text-primaria-900 dark:bg-secundaria-900/80 dark:text-secundaria-50"
        >
            <article class="w-full divide-y lg:w-8/12" ref="container">
                <header class="rounded-t-lg bg-primaria-300 p-3 dark:bg-secundaria-700">
                    <h6 class="text-2xl font-bold">
                        <slot name="header"></slot>
                    </h6>
                </header>

                <div
                    class="max-h-48 overflow-y-auto bg-primaria-50 px-3 py-6 dark:bg-secundaria-900 lg:px-24"
                >
                    <slot name="body"></slot>
                </div>

                <footer
                    class="flex flex-col justify-end space-x-0 space-y-3 rounded-b-lg bg-primaria-300 p-3 dark:bg-secundaria-700 lg:flex-row lg:items-center lg:space-x-3 lg:space-y-0"
                >
                    <slot name="footer"></slot>

                    <ButtonText
                        :texto="__('Cancelar')"
                        @click="fecharModal"
                        dusk="btn-cancelar"
                        especie="inacao"
                        icone="x-circle"
                        type="button"
                    />
                </footer>
            </article>
        </div>
    </Transition>
</template>
