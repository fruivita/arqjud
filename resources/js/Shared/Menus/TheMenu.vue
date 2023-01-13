<!--
    Menu principal.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import MenuGrupo from '@/Shared/Menus/MenuGrupo.vue';
import MenuLink from '@/Shared/Menus/MenuLink.vue';
import { useDadosEstaticosStore } from '@/Stores/DadosEstaticosStore';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';
import { computed } from 'vue';

const __ = useTranslationsStore().__;

const grupos = computed(() => usePage().props.value.auth.menu);
const home = computed(() => usePage().props.value.auth.home);

const logout = () => {
    Inertia.post(usePage().props.value.auth.logout);
};
</script>

<template>
    <Transition
        enter-from-class="opacity-0 scale-90"
        enter-to-class="opacity-100 scale-100"
        enter-active-class="transition duration-300 transform-gpu"
        leave-active-class="transition duration-200 transform-gpu"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-90"
    >
        <nav
            class="fixed inset-0 w-auto space-y-1 overflow-y-auto border-r-4 border-primaria-900 bg-primaria-200 px-3 pt-16 dark:border-secundaria-50 dark:bg-secundaria-700 sm:w-80"
        >
            <header class="mb-6 flex items-center justify-center">
                <InertiaLink
                    :href="home"
                    class="flex h-28 w-28 flex-col items-center justify-center rounded-full bg-primaria-500 font-extrabold text-primaria-50 outline-none transition-all duration-150 hover:bg-primaria-700 focus:ring focus:ring-primaria-300"
                >
                    <span>{{ useDadosEstaticosStore().appNome }}</span>

                    <span>{{ useDadosEstaticosStore().orgaoSigla }}</span>
                </InertiaLink>
            </header>

            <MenuGrupo
                v-for="(grupo, indiceGrupo) in grupos"
                :key="`menu-grupo-${indiceGrupo}`"
                :texto="grupo.nome"
            >
                <MenuLink
                    v-for="(link, indiceLink) in grupo.links"
                    :key="`link-menu-${indiceLink}-${indiceGrupo}`"
                    :ativo="link.ativo"
                    :href="link.href"
                    :icone="link.icone"
                    :texto="link.texto"
                />
            </MenuGrupo>

            <button
                @click="logout"
                class="flex w-full items-center justify-between rounded border-primaria-700 bg-primaria-100 p-2 text-left font-extrabold outline-none transition hover:border-l-4 focus:border-l-4 dark:bg-secundaria-600"
            >
                <span>{{ __('Sair da aplicação') }}</span>

                <Icone nome="door-open" class="h-6 w-6" />
            </button>
        </nav>
    </Transition>
</template>
