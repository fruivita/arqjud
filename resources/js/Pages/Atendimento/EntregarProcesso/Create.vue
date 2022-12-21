<!--
    View para a entrega dos processos solicitadas.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/UseFlash';
import Lotacao from '@/Models/Lotacao';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Alerta from '@/Shared/Containers/Alerta.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import MensagemErro from '@/Shared/Forms/MensagemErro.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { find, first, map, remove, xor } from 'lodash';
import { ref } from 'vue';

const props = defineProps({
    links: { type: Object },
});

const __ = useTranslationsStore().__;

const status = useStatusRequisicaoStore();

const formRecebedor = useForm({ recebedor: '' });

const recebedor = ref('');

// solicitações de processos que o usuário recebedor está autorizado a receber.
const solicitacoes = ref([]);

const formEntregarProcessos = useForm({
    recebedor: '',
    por_guia: false,
    password: '',
    solicitacoes: [],
    // devem ser notificados da entrega dos processos solicitados
    email_terceiros: [],
});

// Solicitações de processo que o usuário está autorizado a receber.
const solicitacoesAutorizadas = async () => {
    if (status.processando == true) {
        flash({ alerta: __('Aguarde a conclusão da solicitação.') });

        return;
    }

    status.setStatus(true);

    formEntregarProcessos.reset();
    formRecebedor.clearErrors();
    solicitacoes.value = '';
    recebedor.value = '';

    await axios
        .post(props.links.solicitacoes, formRecebedor)
        .then(function (resposta) {
            recebedor.value = resposta.data.recebedor;
            formEntregarProcessos.recebedor = resposta.data.recebedor.username;

            if (resposta.data.recebedor.lotacao_id >= 1) {
                solicitacoes.value = resposta.data.solicitacoes;
            }
        })
        .catch(function (erro) {
            switch (erro.response.status) {
                case 401:
                case 419:
                    flash({ erro: __('Autenticação expirada, faça login novamente.') });
                    break;
                case 422: // falha de validação
                    formRecebedor.setError('recebedor', first(erro.response.data.errors.recebedor));
                    break;
                default:
                    flash({ erro: erro.message });
                    console.log(erro);
                    break;
            }
        })
        .finally(() => status.setStatus(false));
};

const seletorChange = (event) => {
    const disponiveis = map(solicitacoes.value, 'id');

    switch (event.target.value) {
        case 'marcar':
            formEntregarProcessos.solicitacoes = disponiveis;
            break;
        case 'desmarcar':
            formEntregarProcessos.solicitacoes = [];
            break;
        case 'inverter':
            formEntregarProcessos.solicitacoes = xor(
                formEntregarProcessos.solicitacoes,
                disponiveis
            );
            break;
    }
};

const formEmail = useForm({ email: '' });

const addEmail = () => {
    if (formEmail.email.length <= 0) {
        return;
    }
    if (find(formEntregarProcessos.email_terceiros, (email) => email == formEmail.email)) {
        formEmail.setError('email', __('Email já informado.'));

        return;
    }

    formEmail.clearErrors();
    formEntregarProcessos.email_terceiros.unshift(formEmail.email);
    formEmail.reset();
};

const removeEmail = (email) => {
    remove(formEntregarProcessos.email_terceiros, (e) => e == email);
};

const clickPorGuia = () => (formEntregarProcessos.password = '');

const entregarRemessas = () => {
    formEntregarProcessos.clearErrors();

    formEntregarProcessos.post(props.links.entregar, {
        preserveScroll: true,
        onSuccess: () => {
            // viewReset();
            flash();
        },
    });
};
</script>

<template>
    <Pagina :titulo="__('Entrega de processos solicitados')">
        <Container>
            <div class="space-y-6">
                <p class="text-right" v-if="links.imprimir_ultima_guia">
                    <a
                        target="_blank"
                        class="underline transition hover:text-primaria-500 focus:text-primaria-500 dark:hover:text-secundaria-400 dark:focus:text-secundaria-400"
                        :href="links.imprimir_ultima_guia"
                        >{{ __('Imprimir última guia') }}</a
                    >
                </p>

                <!-- Dados do recebedor -->
                <form @submit.prevent="solicitacoesAutorizadas" class="flex space-x-3">
                    <div class="w-full">
                        <TextInput
                            v-model="formRecebedor.recebedor"
                            :erro="
                                formRecebedor.errors.recebedor ||
                                formEntregarProcessos.errors.recebedor
                            "
                            :label="__('Recebedor')"
                            :maxlength="20"
                            :placeholder="__('Usuário de rede')"
                            autocomplete="off"
                            icone="person"
                            required
                        />
                    </div>

                    <ButtonIcone
                        :icone="recebedor.id ? 'arrow-clockwise' : 'plus-circle'"
                        dusk="submit"
                        especie="acao"
                        type="submit"
                    />
                </form>

                <div v-if="recebedor.lotacao_id >= 0">
                    <p :class="{ 'text-red-500': formEntregarProcessos.errors.recebedor }">
                        <span>Nome: {{ recebedor.nome ?? __('Sem nome cadastrado') }}</span>
                    </p>

                    <p :class="{ 'text-red-500': recebedor.lotacao_id == 0 }">
                        <span> Lotação: {{ new Lotacao(recebedor.lotacao).nomeExibicao() }} </span>
                    </p>
                </div>

                <!-- Solicitações disponíveis para o recebedor -->
                <div>
                    <Tabela>
                        <template #header>
                            <Heading>
                                <select
                                    @change="seletorChange"
                                    :disabled="solicitacoes.length <= 0"
                                    class="w-14 rounded bg-primaria-300 dark:bg-secundaria-500"
                                >
                                    <option value=""></option>

                                    <option value="marcar">{{ __('Marcar todos') }}</option>

                                    <option value="desmarcar">{{ __('Desmarcar todos') }}</option>

                                    <option value="inverter">{{ __('Inverter seleção') }}</option>
                                </select>
                            </Heading>

                            <Heading :texto="__('Processo')" />

                            <Heading :texto="__('Qtd volumes')" />

                            <Heading :texto="__('GP')" />

                            <Heading :texto="__('Solicitada em')" />

                            <Heading :texto="__('Solicitada por')" />

                            <Heading :texto="__('Lotação destinatária')" />
                        </template>

                        <template #body>
                            <template v-if="solicitacoes.length >= 1">
                                <Row v-for="solicitacao in solicitacoes" :key="solicitacao.id">
                                    <Cell>
                                        <input
                                            v-model="formEntregarProcessos.solicitacoes"
                                            :value="solicitacao.id"
                                            class="h-5 w-5 accent-primaria-500"
                                            type="checkbox"
                                        />
                                    </Cell>

                                    <Cell>{{ solicitacao.processo.numero }}</Cell>

                                    <Cell>{{ solicitacao.processo.qtd_volumes }}</Cell>

                                    <Cell>{{ solicitacao.processo.guarda_permanente }}</Cell>

                                    <Cell>{{ solicitacao.solicitada_em }}</Cell>

                                    <Cell :tooltip="solicitacao.solicitante.nome">
                                        {{ solicitacao.solicitante.username }}
                                    </Cell>

                                    <Cell :tooltip="solicitacao.lotacao_destinataria.nome">
                                        {{ solicitacao.lotacao_destinataria.sigla }}
                                    </Cell>
                                </Row>
                            </template>

                            <template v-else>
                                <Row>
                                    <Cell colspan="7">
                                        {{ __('Nenhum processo solicitado para entrega!') }}
                                    </Cell>
                                </Row>
                            </template>
                        </template>
                    </Tabela>

                    <template v-for="(remessa, indice) in solicitacoes">
                        <p
                            v-if="formEntregarProcessos.errors[`solicitacoes.${indice}`]"
                            class="text-sm font-bold text-red-500"
                        >
                            {{
                                `Solicitação ${indice + 1}: ` +
                                formEntregarProcessos.errors[`solicitacoes.${indice}`]
                            }}
                        </p>
                    </template>
                </div>

                <div v-show="formEntregarProcessos.solicitacoes.length >= 1" class="space-y-6">
                    <!-- Notificação de terceiros -->
                    <div class="space-y-1">
                        <p>{{ __('Notificar terceiros sobre a remessa') }}</p>

                        <form @submit.prevent="addEmail" class="flex space-x-3">
                            <div class="w-full">
                                <TextInput
                                    v-model="formEmail.email"
                                    :erro="formEmail.errors.email"
                                    :label="__('Email de terceiros')"
                                    :maxlength="50"
                                    autocomplete="off"
                                    dusk="email"
                                    icone="envelope"
                                    type="email"
                                />
                            </div>

                            <ButtonIcone
                                dusk="submit"
                                especie="acao"
                                icone="plus-circle"
                                type="submit"
                            />
                        </form>

                        <template v-if="formEntregarProcessos.email_terceiros.length >= 1">
                            <div class="flex flex-wrap justify-between">
                                <div
                                    class="py-1"
                                    v-for="(email, indice) in formEntregarProcessos.email_terceiros"
                                    :key="email"
                                >
                                    <div class="space-x-2 hover:underline">
                                        <span>{{ email }}</span>

                                        <ButtonIcone
                                            @click="removeEmail(email)"
                                            dusk="submit"
                                            especie="perigo"
                                            icone="x-circle"
                                            type="button"
                                        />
                                    </div>

                                    <MensagemErro
                                        v-if="
                                            formEntregarProcessos.errors[
                                                `email_terceiros.${indice}`
                                            ]
                                        "
                                        :erro="
                                            formEntregarProcessos.errors[
                                                `email_terceiros.${indice}`
                                            ]
                                        "
                                    />
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Entrega por guia -->
                    <div>
                        <CheckBox
                            @click="clickPorGuia"
                            v-model="formEntregarProcessos.por_guia"
                            :label="__('Entrega por guia')"
                        />

                        <MensagemErro
                            v-if="formEntregarProcessos.errors.por_guia"
                            :erro="formEntregarProcessos.errors.por_guia"
                        />
                    </div>

                    <Alerta v-show="formEntregarProcessos.por_guia">
                        <p>
                            {{
                                __(
                                    'A entrega por guia pressupõe que os processos serão entregues na lotação destinatária por meio de guia impressa.'
                                )
                            }}
                        </p>

                        <p>
                            {{
                                __(
                                    'Portanto, a verdade dos fatos estará fora da aplicação, isto é, na guia de remessa de processos.'
                                )
                            }}
                        </p>

                        <span>{{ __('Orientações:') }}</span>

                        <ol class="list-inside list-decimal">
                            <li>
                                {{
                                    __(
                                        'Identifique previamente quem irá receber os processos na lotação destinatária e informe no campo Recebedor acima;'
                                    )
                                }}
                            </li>

                            <li>{{ __('Imprima a guia de remessa gerada pela aplicação;') }}</li>

                            <li>
                                {{
                                    __(
                                        'Colha a assinatura do recebedor na guia no ato da entrega dos processos;'
                                    )
                                }}
                            </li>

                            <li>
                                {{
                                    __(
                                        'Guarde a guia de remessa assinada até a devolução e conferência de todos os processos remetidos.'
                                    )
                                }}
                            </li>
                        </ol>
                    </Alerta>

                    <!-- Submete a solicitação -->
                    <form @submit.prevent="entregarRemessas" class="flex space-x-3">
                        <TextInput
                            v-if="!formEntregarProcessos.por_guia"
                            v-model="formEntregarProcessos.password"
                            :erro="formEntregarProcessos.errors.password"
                            :label="__('Senha de rede do recebedor')"
                            :maxlength="50"
                            autocomplete="current-password"
                            dusk="password"
                            icone="key"
                            type="password"
                            required
                        />

                        <ButtonText
                            :texto="__('Entregar processos')"
                            dusk="submit"
                            especie="acao"
                            icone="cart"
                            type="submit"
                        />
                    </form>
                </div>
            </div>
        </Container>
    </Pagina>
</template>
