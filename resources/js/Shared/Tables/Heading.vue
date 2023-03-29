<!--
    Heading comum (th) da tabela padrão.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->
<script setup>
const props = defineProps({
    fixo: { type: Boolean, default: false },
    texto: { type: String, default: '' },
});
</script>

<template>
    <th
        :class="{
            'sticky left-0 z-10 h-fit bg-primaria-200 dark:bg-secundaria-700': fixo,
        }"
        class="p-3"
    >
        <span v-if="texto" class="font-bold">{{ texto }}</span>

        <slot></slot>
    </th>
</template>
