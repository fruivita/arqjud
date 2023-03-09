<!--
    Rodapé da aplicação.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->
<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import { useDadosEstaticosStore } from '@/Stores/DadosEstaticosStore';
import { useTranslationsStore } from '@/Stores/TranslationsStore';

const __ = useTranslationsStore().__;
</script>

<template>
    <footer class="bg-primaria-100 px-3 py-6 text-center text-sm font-bold dark:bg-secundaria-800">
        <div class="space-y-3">
            <p>
                {{
                    `${useDadosEstaticosStore().appNome} - ${
                        useDadosEstaticosStore().appNomeCompleto
                    }`
                }}
            </p>

            <div class="flex items-center justify-center space-x-3">
                <a class="space-x-1" href="https://github.com/fruivita/arqjud/wiki">
                    <Icone class="inline h-6 w-6" nome="book" />

                    <span class="hover:underline">{{ __('Documentação') }}</span>
                </a>

                <a class="space-x-1" href="https://github.com/fruivita/arqjud">
                    <Icone class="inline h-6 w-6" nome="git" />

                    <span class="hover:underline">{{ useDadosEstaticosStore().appVersao }}</span>
                </a>
            </div>
        </div>
    </footer>
</template>
