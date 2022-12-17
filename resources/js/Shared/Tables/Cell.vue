<!--
    Célula (td) da tabela padrão.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Tooltip from '@/Shared/Misc/Tooltip.vue';

defineProps({
    erro: { type: String, default: '' },
    tooltip: { type: String },
    tooltip: { type: [Array, String] },
});
</script>

<template>
    <td class="p-3" dusk="slot">
        <div class="flex items-center justify-center">
            <slot></slot>

            <Tooltip v-if="tooltip" :texto="tooltip" class="ml-1" />
        </div>

        <span v-if="erro" class="block text-sm font-bold text-red-500">{{ erro }}</span>
    </td>
</template>
