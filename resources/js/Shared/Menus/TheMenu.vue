<!--
    Menu principal.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import MenuGrupo from '@/Shared/Menus/MenuGrupo.vue';
import MenuLink from '@/Shared/Menus/MenuLink.vue';
import { usePage } from '@inertiajs/inertia-vue3';
import { computed } from 'vue';

const grupos = computed(() => usePage().props.value.auth.menu);
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
            class="fixed inset-0 w-72 space-y-1 overflow-y-auto border-r-4 border-primaria-900 bg-primaria-200 px-3 pt-16 dark:border-secundaria-50 dark:bg-secundaria-700"
        >
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
        </nav>
    </Transition>
</template>
