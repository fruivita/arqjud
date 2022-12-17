<!--
    View para a movimentação de processos entre diferentes caixas.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
-->

<script setup>
import { flash } from '@/Composables/UseFlash';
import { mascaraCNJ, maxViewItems } from '@/keys.js';
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
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { find, first, pick, remove, slice } from 'lodash';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    localidades: { type: Object },
    links: { type: Object },
});

const __ = useTranslationsStore().__;

const status = useStatusRequisicaoStore();

const formProcesso = useForm({ numero: '' });

const formCaixaDestino = useForm({
    numero: '',
    ano: '',
    guarda_permanente: false,
    complemento: '',
    localidade_criadora_id: '',
});

watch(formCaixaDestino, () => {
    formMoverProcessos.reset('volume_id');
    caixaDestino.value = '';
});

const formMoverProcessos = useForm({
    volume_id: '',
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
                pick(resposta.data.processo, ['numero', 'guarda_permanente'])
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

const caixaDestino = ref('');

const getCaixaDestino = async () => {
    if (status.processando == true) {
        flash({ alerta: __('Aguarde a conclusão da solicitação.') });

        return;
    }

    status.setStatus(true);

    formMoverProcessos.volume_id = '';
    formCaixaDestino.clearErrors();

    await axios
        .post(props.links.search.caixa, formCaixaDestino)
        .then(function (resposta) {
            caixaDestino.value = resposta.data.caixa;
        })
        .catch(function (erro) {
            caixaDestino.value = '';

            switch (erro.response.status) {
                case 401:
                case 419:
                    flash({ erro: __('Autenticação expirada, faça login novamente.') });
                    break;
                case 422: // falha de validação
                    formCaixaDestino.setError({
                        numero: first(erro.response.data.errors.numero),
                        ano: first(erro.response.data.errors.ano),
                        guarda_permanente: first(erro.response.data.errors.guarda_permanente),
                        complemento: first(erro.response.data.errors.complemento),
                        localidade_criadora_id: first(
                            erro.response.data.errors.localidade_criadora_id
                        ),
                    });
                    break;
                default:
                    flash({ erro: erro.message });
                    console.log(erro);
                    break;
            }
        })
        .finally(() => status.setStatus(false));
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
    formMoverProcessos.reset();
    formCaixaDestino.reset();
    formProcesso.reset();
    caixaDestino.value = '';
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
                                <Clipboard :copiavel="processo.numero" />
                            </Cell>

                            <Cell>{{ processo.guarda_permanente }}</Cell>

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

                    <form @submit.prevent="getCaixaDestino">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                                <NumeroInput
                                    v-model="formCaixaDestino.numero"
                                    :erro="formCaixaDestino.errors.numero"
                                    :label="__('Número da caixa')"
                                    :max="9999999"
                                    :min="1"
                                    :placeholder="__('Apenas números')"
                                    autocomplete="off"
                                    icone="box2"
                                    required
                                />

                                <NumeroInput
                                    v-model="formCaixaDestino.ano"
                                    :erro="formCaixaDestino.errors.ano"
                                    :label="__('Ano da caixa')"
                                    :max="new Date().getFullYear()"
                                    :min="1900"
                                    :placeholder="__('aaaa')"
                                    autocomplete="off"
                                    icone="calendar-range"
                                    required
                                />

                                <TextInput
                                    v-model="formCaixaDestino.complemento"
                                    :erro="formCaixaDestino.errors.complemento"
                                    :label="__('Complemento do número')"
                                    :maxlength="50"
                                    :placeholder="__('Ex.: Cri, Civ, ...')"
                                    autocomplete="off"
                                    icone="quote"
                                />

                                <DropDown
                                    v-model="formCaixaDestino.localidade_criadora_id"
                                    :erro="formCaixaDestino.errors.localidade_criadora_id"
                                    :label="__('Localidade criadora')"
                                    :opcoes="localidades.data"
                                    icone="pin-map"
                                    labelOpcao="nome"
                                    required
                                />

                                <div
                                    class="flex flex-col justify-between space-y-3 space-x-0 md:flex-row md:space-x-3 md:space-y-0"
                                >
                                    <CheckBox
                                        v-model="formCaixaDestino.guarda_permanente"
                                        :label="__('Guarda Permanente')"
                                    />

                                    <ButtonText
                                        :texto="__('Carregar volumes')"
                                        dusk="submit"
                                        especie="acao"
                                        icone="arrow-clockwise"
                                        type="submit"
                                    />
                                </div>
                            </div>

                            <Alerta>
                                <p>
                                    {{
                                        __(
                                            'Todos os processos movimentados assumirão o status de guarda da caixa de destino, não importanto seu status anterior. Ou seja, neste caso, todos os processos movidos :attribute SERÃO de guarda permanente.',
                                            {
                                                attribute: formCaixaDestino.guarda_permanente
                                                    ? ''
                                                    : __('NÃO'),
                                            }
                                        )
                                    }}
                                </p>
                            </Alerta>
                        </div>
                    </form>

                    <form
                        v-show="caixaDestino.volumes"
                        @submit.prevent="moverProcessos"
                        class="flex flex-col justify-between space-y-3 space-x-0 md:flex-row md:space-x-3 md:space-y-0"
                    >
                        <div class="w-full md:w-1/2">
                            <DropDown
                                v-model="formMoverProcessos.volume_id"
                                :erro="formMoverProcessos.errors.volume_id"
                                :label="__('Volume de destino')"
                                :opcoes="caixaDestino.volumes ?? []"
                                icone="boxes"
                                labelOpcao="numero"
                                required
                            />
                        </div>

                        <Transition
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            enter-active-class="transition duration-300 transform-gpu"
                            leave-active-class="transition duration-200 transform-gpu"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <ButtonText
                                v-show="formMoverProcessos.volume_id >= 1"
                                :texto="__('Mover processos')"
                                dusk="submit"
                                especie="acao"
                                icone="joystick"
                                type="submit"
                            />
                        </Transition>
                    </form>
                </div>
            </Container>
        </Transition>
    </Pagina>
</template>
