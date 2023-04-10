<!--
    View para a movimentação de processos entre diferentes caixas.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
-->

<script setup>
import { flash } from '@/Composables/UseFlash';
import { gp } from '@/Helpers/Processo';
import { mascaraCNJ, maxViewItems } from '@/keys';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Alerta from '@/Shared/Containers/Alerta.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import DropDown from '@/Shared/Forms/DropDown.vue';
import NumeroInput from '@/Shared/Forms/NumeroInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import Clipboard from '@/Shared/Misc/Clipboard.vue';
import Tooltip from '@/Shared/Misc/Tooltip.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { find, first, pick, remove, slice } from 'lodash';
import { computed, ref } from 'vue';

const props = defineProps({
    localidades: { type: Object },
    tipos_processo: { type: Object },
    links: { type: Object },
});

const __ = useTranslationsStore().__;

const status = useStatusRequisicaoStore();

const formProcesso = useForm({ numero: '' });

const formMoverProcessos = useForm({
    numero: '',
    ano: '',
    guarda_permanente: false,
    complemento: '',
    localidade_criadora_id: '',
    tipo_processo_id: '',
    processos: [],
});

// exibe todos os processos ou apenas alguns (últimos X adicionados)
const exibirTodos = ref(false);

// processos em exibição na view de acordo com o valor de exibirTodos
const processos = computed(() =>
    exibirTodos.value
        ? formMoverProcessos.processos
        : slice(formMoverProcessos.processos, 0, maxViewItems)
);

const addProcesso = async () => {
    if (status.processando == true) {
        flash({ alerta: __('Aguarde a conclusão da solicitação.') });

        return;
    }

    if (formProcesso.numero.length != 25) {
        formProcesso.setError('numero', __('Informe o número completo do processo no padrão CNJ.'));

        return;
    }

    status.setStatus(true);
    formProcesso.clearErrors();

    if (find(formMoverProcessos.processos, { numero: formProcesso.numero })) {
        formProcesso.setError('numero', __('Processo já informado.'));

        status.setStatus(false);

        return;
    }

    await axios
        .post(props.links.search.processo, formProcesso)
        .then(function (resposta) {
            formMoverProcessos.processos.unshift(
                pick(resposta.data.processo, ['numero', 'guarda_permanente', 'numero_antigo'])
            );
            formProcesso.reset();
        })
        .catch(function (erro) {
            switch (erro.response.status) {
                case 401:
                case 419:
                    flash({ erro: __('Autenticação expirada, faça login novamente.') });
                    break;
                case 422: // falha de validação
                    formProcesso.setError('numero', first(erro.response.data.errors.numero));
                    break;
                default:
                    flash({ erro: erro.message });
                    console.log(erro);
                    break;
            }
        })
        .finally(() => status.setStatus(false));
};

const removeProcesso = (numero) => {
    remove(formMoverProcessos.processos, (p) => p.numero == numero);
};

const moverProcessos = () => {
    formMoverProcessos.clearErrors();

    formMoverProcessos.post(props.links.store, {
        preserveScroll: true,
        onSuccess: () => {
            viewReset();
            flash();
        },
    });
};

const viewReset = () => {
    formProcesso.reset();
    formMoverProcessos.reset();
    exibirTodos.value = false;
};
</script>

<template>
    <Pagina :titulo="__('Movimentação de processos entre caixas')">
        <Container>
            <form @submit.prevent="addProcesso" class="mb-6 flex space-x-3">
                <div class="w-full">
                    <TextInput
                        v-model="formProcesso.numero"
                        :erro="formProcesso.errors.numero"
                        :label="__('Processo')"
                        :mascara="mascaraCNJ"
                        :maxlength="25"
                        :placeholder="__('Apenas números')"
                        autocomplete="off"
                        icone="journal-bookmark"
                        required
                    />
                </div>

                <ButtonIcone dusk="submit" especie="acao" icone="plus-circle" type="submit" />
            </form>

            <Tabela>
                <template #header>
                    <Heading
                        :texto="
                            __('Processos que serão movimentados: :attribute', {
                                attribute: formMoverProcessos.processos.length,
                            })
                        "
                    />

                    <Heading :texto="__('GP')" />

                    <Heading :texto="__('Remover')" />
                </template>

                <template #body>
                    <template v-if="processos.length">
                        <Row v-for="(processo, indice) in processos" :key="processo.numero">
                            <Cell :erro="formMoverProcessos.errors[`processos.${indice}.numero`]">
                                <span>{{ processo.numero }}</span>

                                <Clipboard :copiavel="processo.numero" class="ml-1" />

                                <Tooltip
                                    v-if="processo.numero_antigo"
                                    :texto="processo.numero_antigo"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell>{{ gp(processo) }}</Cell>

                            <Cell>
                                <ButtonIcone
                                    @click="removeProcesso(processo.numero)"
                                    especie="perigo"
                                    icone="dash-circle"
                                />
                            </Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell colspan="3">{{ __('Nenhum processo para movimentação!') }}</Cell>
                        </Row>
                    </template>
                </template>
            </Tabela>

            <button
                v-show="formMoverProcessos.processos.length > maxViewItems"
                @click="exibirTodos = !exibirTodos"
                class="p-3 hover:underline focus:underline"
            >
                <span v-if="exibirTodos">{{ __('Recolher') }}</span>

                <span v-else>{{ __('Exibir todos') }}</span>
            </button>
        </Container>

        <Transition
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            enter-active-class="transition duration-300 transform-gpu"
            leave-active-class="transition duration-200 transform-gpu"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <Container v-show="formMoverProcessos.processos.length">
                <div class="space-y-6">
                    <h3 class="text-center font-bold">{{ __('Caixa de destino') }}</h3>

                    <form @submit.prevent="moverProcessos">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                                <NumeroInput
                                    v-model="formMoverProcessos.numero"
                                    :erro="formMoverProcessos.errors.numero"
                                    :label="__('Número da caixa')"
                                    :max="9999999"
                                    :min="1"
                                    :placeholder="__('Apenas números')"
                                    autocomplete="off"
                                    icone="box2"
                                    required
                                />

                                <NumeroInput
                                    v-model="formMoverProcessos.ano"
                                    :erro="formMoverProcessos.errors.ano"
                                    :label="__('Ano da caixa')"
                                    :max="new Date().getFullYear()"
                                    :min="1900"
                                    :placeholder="__('aaaa')"
                                    autocomplete="off"
                                    icone="calendar-range"
                                    required
                                />

                                <DropDown
                                    v-model="formMoverProcessos.localidade_criadora_id"
                                    :erro="formMoverProcessos.errors.localidade_criadora_id"
                                    :label="__('Localidade criadora')"
                                    :opcoes="localidades.data"
                                    icone="pin-map"
                                    labelOpcao="nome"
                                    required
                                />

                                <DropDown
                                    v-model="formMoverProcessos.tipo_processo_id"
                                    :erro="formMoverProcessos.errors.tipo_processo_id"
                                    :label="__('Tipo de processo')"
                                    :opcoes="tipos_processo.data"
                                    icone="card-list"
                                    labelOpcao="nome"
                                    required
                                />

                                <TextInput
                                    v-model="formMoverProcessos.complemento"
                                    :erro="formMoverProcessos.errors.complemento"
                                    :label="__('Complemento do número')"
                                    :maxlength="50"
                                    :placeholder="__('Ex.: Cri, Civ, ...')"
                                    autocomplete="off"
                                    icone="quote"
                                />

                                <CheckBox
                                    v-model:checked="formMoverProcessos.guarda_permanente"
                                    :label="__('Guarda Permanente')"
                                />
                            </div>

                            <Alerta>
                                <p>
                                    {{
                                        __(
                                            'Todos os processos movimentados assumirão o status de guarda da caixa de destino, não importanto seu status anterior. Ou seja, neste caso, todos os processos movidos :attribute SERÃO de guarda permanente.',
                                            {
                                                attribute: formMoverProcessos.guarda_permanente
                                                    ? ''
                                                    : __('NÃO'),
                                            }
                                        )
                                    }}
                                </p>
                            </Alerta>

                            <ButtonText
                                :texto="__('Mover processos')"
                                dusk="submit"
                                especie="acao"
                                icone="joystick"
                                type="submit"
                            />
                        </div>
                    </form>
                </div>
            </Container>
        </Transition>
    </Pagina>
</template>
