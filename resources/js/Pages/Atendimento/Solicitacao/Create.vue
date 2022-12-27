<!--
    View para a solicitação de processos em nome de terceiros.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/UseFlash';
import { mascaraCNJ, maxViewItems } from '@/keys';
import Lotacao from '@/Models/Lotacao';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
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
    links: { type: Object },
});

const __ = useTranslationsStore().__;

const status = useStatusRequisicaoStore();

const formSolicitante = useForm({ solicitante: '' });

const solicitante = ref('');

const formProcesso = useForm({ numero: '' });

const formSolicitarProcessos = useForm({
    solicitante_id: '',
    destino_id: '',
    processos: [],
});

// exibe todos os processos ou apenas alguns (últimos X adicionados)
const exibirTodos = ref(false);

// processos em exibição na view de acordo com o valor de exibirTodos
const processos = computed(() =>
    exibirTodos.value
        ? formSolicitarProcessos.processos
        : slice(formSolicitarProcessos.processos, 0, maxViewItems)
);

const getSolicitante = async () => {
    if (status.processando == true) {
        flash({ alerta: __('Aguarde a conclusão da solicitação.') });

        return;
    }

    status.setStatus(true);

    formSolicitarProcessos.reset();
    formSolicitante.clearErrors();

    await axios
        .post(props.links.solicitante, formSolicitante)
        .then(function (resposta) {
            solicitante.value = resposta.data.solicitante;

            if (resposta.data.solicitante.lotacao_id >= 1) {
                formSolicitarProcessos.solicitante_id = resposta.data.solicitante.id;
                formSolicitarProcessos.destino_id = resposta.data.solicitante.lotacao_id;
            }
        })
        .catch(function (erro) {
            solicitante.value = '';

            switch (erro.response.status) {
                case 401:
                case 419:
                    flash({ erro: __('Autenticação expirada, faça login novamente.') });
                    break;
                case 422: // falha de validação
                    formSolicitante.setError(
                        'solicitante',
                        first(erro.response.data.errors.solicitante)
                    );
                    break;
                default:
                    flash({ erro: erro.message });
                    console.log(erro);
                    break;
            }
        })
        .finally(() => status.setStatus(false));
};

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

    if (find(formSolicitarProcessos.processos, { numero: formProcesso.numero })) {
        formProcesso.setError('numero', __('Processo já informado.'));

        status.setStatus(false);

        return;
    }

    await axios
        .post(props.links.processo, formProcesso)
        .then(function (resposta) {
            formSolicitarProcessos.processos.unshift(
                pick(resposta.data.processo, ['numero', 'numero_antigo'])
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
    remove(formSolicitarProcessos.processos, (p) => p.numero == numero);
};

const solicitarProcessos = () => {
    formSolicitarProcessos.clearErrors();

    formSolicitarProcessos.post(props.links.store, {
        preserveScroll: true,
        onSuccess: () => {
            viewReset();
            flash();
        },
    });
};

const viewReset = () => {
    formSolicitante.reset();
    formProcesso.reset();
    formSolicitarProcessos.reset();
    exibirTodos.value = false;
};
</script>

<template>
    <Pagina :titulo="__('Solicitação de processos')">
        <Container>
            <div class="space-y-6">
                <form @submit.prevent="getSolicitante" class="flex space-x-3">
                    <div class="w-full">
                        <TextInput
                            v-model="formSolicitante.solicitante"
                            :erro="formSolicitante.errors.solicitante"
                            :label="__('Solicitante')"
                            :maxlength="20"
                            :placeholder="__('Usuário de rede')"
                            autocomplete="off"
                            icone="person"
                            required
                        />
                    </div>

                    <ButtonIcone
                        :icone="solicitante.id ? 'arrow-clockwise' : 'plus-circle'"
                        dusk="submit"
                        especie="acao"
                        type="submit"
                    />
                </form>

                <div v-if="solicitante">
                    <p>
                        {{
                            __('Nome: :attribute', {
                                attribute: solicitante.nome ?? __('Sem nome cadastrado'),
                            })
                        }}
                    </p>

                    <p>
                        {{
                            __('Destino: :attribute', {
                                attribute: new Lotacao(solicitante.lotacao).nomeExibicao(),
                            })
                        }}
                    </p>
                </div>
            </div>
        </Container>

        <Transition
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            enter-active-class="transition duration-300 transform-gpu"
            leave-active-class="transition duration-200 transform-gpu"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <Container
                v-show="formSolicitarProcessos.solicitante_id && formSolicitarProcessos.destino_id"
            >
                <form
                    @submit.prevent="addProcesso"
                    class="mb-6 flex flex-row justify-between space-x-3"
                >
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
                                __('Processos que serão solicitados: :attribute', {
                                    attribute: formSolicitarProcessos.processos.length,
                                })
                            "
                        />

                        <Heading :texto="__('Remover')" />
                    </template>

                    <template #body>
                        <template v-if="processos.length">
                            <Row v-for="(processo, indice) in processos" :key="processo.numero">
                                <Cell
                                    :erro="
                                        formSolicitarProcessos.errors[`processos.${indice}.numero`]
                                    "
                                >
                                    <span>{{ processo.numero }}</span>

                                    <Clipboard :copiavel="processo.numero" class="ml-1" />

                                    <Tooltip
                                        v-if="processo.numero_antigo"
                                        :texto="processo.numero_antigo"
                                        class="ml-1"
                                    />
                                </Cell>

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
                                <Cell colspan="2">{{ __('Nenhum processo solicitado!') }}</Cell>
                            </Row>
                        </template>
                    </template>
                </Tabela>

                <div class="mt-3 flex justify-end space-x-3">
                    <button
                        v-show="formSolicitarProcessos.processos.length > maxViewItems"
                        @click="exibirTodos = !exibirTodos"
                        class="hover:underline focus:underline"
                    >
                        <span v-if="exibirTodos">{{ __('Recolher') }}</span>

                        <span v-else>{{ __('Exibir todos') }}</span>
                    </button>

                    <ButtonText
                        v-show="formSolicitarProcessos.processos.length >= 1"
                        :texto="__('Solicitar processo')"
                        @click="solicitarProcessos"
                        dusk="submit"
                        especie="acao"
                        icone="signpost"
                        type="button"
                    />
                </div>
            </Container>
        </Transition>
    </Pagina>
</template>
