<!--
    Tabela (table) padrão.

    Notar que o componente depende do status do processamento geral da
    aplicação para definir seu próprio estado.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';

const status = useStatusRequisicaoStore();
</script>

<template>
    <div class="overflow-x-auto">
        <table
            :class="{
                'opacity-50': status.processando === true,
            }"
            class="w-full text-center"
        >
            <thead class="bg-primaria-200 dark:bg-secundaria-700">
                <tr dusk="header">
                    <slot name="header"></slot>
                </tr>
            </thead>

            <tbody dusk="body">
                <slot name="body"></slot>
            </tbody>
        </table>
    </div>
</template>
