<!--
    Componente para exibição de resultados paginados.

    Notar que os dados são paginados pelo backend Laravel, portanto, os dados
    estão no padrão definido pelo próprio framework.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { computed } from '@vue/reactivity';

const props = defineProps({
    elementos: { type: Object },
});

const ultimo = computed(() => props.elementos.links.length - 1);
</script>

<template>
    <div class="flex items-end justify-between space-x-3 p-3">
        <div>
            <p class="text-sm font-bold">
                <span dusk="from">{{ elementos.from }}</span>
                -
                <span dusk="to">{{ elementos.to }}</span>
                de
                <span dusk="total">{{ elementos.total }}</span>
            </p>
        </div>

        <div
            class="flex divide-x-2 divide-primaria-300 rounded border-4 border-primaria-300 dark:divide-secundaria-400 dark:border-secundaria-400"
        >
            <Component
                v-for="(link, index) in elementos.links"
                :key="`page-link-${index}`"
                :class="{
                    'hidden lg:block': !(index === 0 || index === ultimo || link.active),
                    'opacity-20': !link.url,
                    'hover:bg-primaria-400 focus:bg-primaria-400 dark:hover:bg-secundaria-800 dark:focus:bg-secundaria-800':
                        link.url,
                    'bg-primaria-400 dark:bg-secundaria-800': link.active,
                    'bg-primaria-200  dark:bg-secundaria-600': !link.active,
                }"
                :href="link.url"
                :is="link.url ? 'InertiaLink' : 'span'"
                v-html="link.label"
                class="transform-gpu px-3 py-1 outline-none transition duration-300"
                dusk="pagina"
                preserve-scroll
            />
        </div>
    </div>
</template>
