<!--
    Input de texto com ícone (opcional), mensagem de erro de validação e contador de
    caracteres.

    O input é destinado apenas a inputs do tipo text ou password.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
 -->

<script>
export default { inheritAttrs: false };
</script>

<script setup>
import { gerarID } from '@/Composables/UseGerarID';
import ContadorCaracteres from '@/Shared/Forms/ContadorCaracteres.vue';
import MensagemErro from '@/Shared/Forms/MensagemErro.vue';
import Icone from '@/Shared/Icones/Icone.vue';
import { mask } from 'maska';
import { computed, ref } from 'vue';

const props = defineProps({
    id: { type: String },
    icone: { type: String },
    erro: { type: String },
    label: { type: String },
    mascara: { type: String },
    maxlength: { type: [Number, String], required: true },
    modelValue: { type: String },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    type: {
        type: String,
        default: 'text',
        validator(value) {
            return ['text', 'password', 'email'].includes(value);
        },
    },
});

const emit = defineEmits(['update:modelValue']);

const exibirContador = ref(false);

const idReal = computed(() => props.id ?? gerarID());

const onInput = (event) => {
    if (!props.disabled) {
        if (props.mascara) {
            emit('update:modelValue', mask(event.target.value, props.mascara));
        } else {
            emit('update:modelValue', event.target.value);
        }
    }
};
</script>

<template>
    <div class="space-y-1">
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
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
            >
                <Icone :nome="icone" />
            </div>

            <input
                :id="idReal"
                :class="{
                    'pl-12': icone,
                    'pl-2': !icone,
                    'border-primaria-500 bg-primaria-100 ring-primaria-500': !erro,
                    'border-red-500 bg-red-100 ring-red-500': erro,
                    'border-dashed': disabled,
                }"
                :disabled="disabled"
                :maxlength="maxlength"
                :required="required"
                :type="type"
                :value="modelValue"
                @blur="exibirContador = false"
                @focus="exibirContador = true"
                @input="onInput"
                v-bind="$attrs"
                class="w-full rounded-lg border py-2 pr-20 outline-none transition duration-300 focus:ring disabled:cursor-not-allowed disabled:bg-primaria-50 dark:disabled:bg-secundaria-900"
            />

            <ContadorCaracteres
                v-show="exibirContador"
                :maxlength="maxlength"
                :texto="modelValue"
            />
        </div>

        <MensagemErro v-if="erro" :erro="erro" />
    </div>
</template>
