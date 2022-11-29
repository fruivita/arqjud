<!--
    Componente Select com ícone (opcional).

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
 -->

<script>
export default { inheritAttrs: false };
</script>

<script setup>
import { gerarID } from '@/Composables/UseGerarID';
import MensagemErro from '@/Shared/Forms/MensagemErro.vue';
import Icone from '@/Shared/Icones/Icone.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { get, isArray, join, pick, values } from 'lodash';
import { computed } from 'vue';

const props = defineProps({
    opcoes: { type: Array, required: true },
    id: { type: String },
    icone: { type: String },
    erro: { type: String },
    label: { type: String },
    modelValue: { type: [Number, String] },
    labelOpcao: { type: [Array, String], default: 'nome' },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
});

const __ = useTranslationsStore().__;

const emit = defineEmits(['update:modelValue']);

const idReal = computed(() => props.id ?? gerarID());

const optionText = (opcao) => {
    let texto = '';

    if (isArray(props.labelOpcao)) {
        texto = join(values(pick(opcao, props.labelOpcao)), '/');
    } else {
        texto = get(opcao, props.labelOpcao);
    }

    return texto;
};

const onChange = (event) => {
    if (!props.disabled) {
        emit('update:modelValue', event.target.value);
    }
};
</script>

<template>
    <div class="w-full space-y-1">
        <label v-if="label" :for="idReal" class="font-bold" dusk="label">
            {{ label }}

            <span v-if="required" class="text-red-500" dusk="required">*</span>
        </label>

        <div
            :class="{
                'dark:text-primaria-900': !disabled,
                'dark:text-primaria-50': disabled,
            }"
            class="relative"
        >
            <div
                v-if="icone"
                class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3"
            >
                <Icone :nome="icone" />
            </div>

            <select
                :id="idReal"
                :class="{
                    'pl-12': icone,
                    'pl-2': !icone,
                    'border-primaria-500 bg-primaria-100 ring-primaria-500': !erro,
                    'border-red-500 bg-red-100 ring-red-500': erro,
                    'border-dashed': disabled,
                }"
                :disabled="disabled"
                :required="required"
                :value="modelValue"
                @change="onChange"
                v-bind="$attrs"
                class="w-full rounded-lg border py-2 opacity-100 outline-none transition duration-300 focus:ring disabled:cursor-not-allowed disabled:bg-primaria-50 dark:disabled:bg-secundaria-900"
            >
                <template v-if="opcoes.length">
                    <option v-for="opcao in opcoes" :key="opcao.id" :value="opcao.id">
                        {{ optionText(opcao) }}
                    </option>
                </template>

                <template v-else>
                    <option value="-1" selected>{{ __('Nenhum registro encontrado') }}</option>
                </template>
            </select>
        </div>

        <MensagemErro v-if="erro" :erro="erro" />
    </div>
</template>
