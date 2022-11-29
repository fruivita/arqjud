<!--
    Container para as preferências de visualização da tabelas.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Container from '@/Shared/Containers/Container.vue';
import Icone from '@/Shared/Icones/Icone.vue';
import PorPagina from '@/Shared/Tables/PorPagina.vue';
import { onClickOutside } from '@vueuse/core';
import { ref } from 'vue';

const visivel = ref(false);

const container = ref(null);

onClickOutside(container, () => (visivel.value = false));
</script>

<template>
    <div class="flex justify-end">
        <div class="inline-flex flex-col text-right" ref="container">
            <div>
                <button
                    @click="visivel = !visivel"
                    class="transform-gpu opacity-50 transition duration-300 hover:opacity-100"
                    dusk="toggle"
                >
                    <Icone nome="three-dots-vertical" />
                </button>
            </div>

            <Transition
                enter-from-class="opacity-0 scale-50"
                enter-to-class="opacity-100 scale-100"
                enter-active-class="transition duration-300 transform-gpu"
                leave-active-class="transition duration-200 transform-gpu"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-50"
            >
                <Container v-show="visivel" dusk="preferencias">
                    <div class="inline-flex">
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2" dusk="slot">
                                <slot></slot>
                            </div>

                            <PorPagina />
                        </div>
                    </div>
                </Container>
            </Transition>
        </div>
    </div>
</template>
