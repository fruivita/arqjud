<!--
    Célula (td) da tabela padrão.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
defineProps({
    erro: { type: String, default: '' },
    fixo: { type: Boolean, default: false },
});
</script>

<template>
    <td :class="{ 'sticky left-0 z-10 h-fit': fixo }" class="p-3" dusk="slot">
        <div class="flex items-center justify-center">
            <slot></slot>
        </div>

        <span v-if="erro" class="block text-sm font-bold text-red-500">{{ erro }}</span>
    </td>
</template>
