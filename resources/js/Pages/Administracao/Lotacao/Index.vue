<!--
    View para listagem (com filtros) das Lotações.

    Notar que:
    - As preferências de exibição são armazenadas no cache do navegador.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
    @see https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { countElementosVisiveis } from '@/Composables/UseCountElementosVisiveis';
import { flash } from '@/Composables/UseFlash';
import { useOrdenacao } from '@/Composables/UseOrdenacao';
import { eAdministravel } from '@/Helpers/Lotacao';
import { perPageKey, updatePerPageKey } from '@/keys';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import Alerta from '@/Shared/Containers/Alerta.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import Pesquisa from '@/Shared/Forms/Pesquisa.vue';
import Tooltip from '@/Shared/Misc/Tooltip.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import HeadingOrdenavel from '@/Shared/Tables/HeadingOrdenavel.vue';
import Paginacao from '@/Shared/Tables/Paginacao.vue';
import Preferencia from '@/Shared/Tables/Preferencia.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';
import { useLocalStorage } from '@vueuse/core';
import { merge, pickBy } from 'lodash';
import { computed, provide, readonly, ref, watch } from 'vue';

const props = defineProps({
    lotacoes: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.lotacoes.meta.termo ?? '');

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.lotacoes.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    lotacao: true,
    sigla: true,
    administravel: true,
    lotacaoPai: true,
    usuarios: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.lotacoes.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.lotacoes.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['lotacoes'],
        }
    );
};

const administravel = (url) => {
    Inertia.patch(
        url,
        {},
        {
            preserveScroll: true,
            preserveState: true,

            onSuccess: () => flash(),
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="__('Lotações')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Alerta>
            <p>
                {{
                    __(
                        'Somente nas lotações administráveis é possível alterar o perfil de seus usuários.'
                    )
                }}
            </p>

            <p>
                {{
                    __(
                        'Ao se tornar uma lotação NÃO administrável, o perfil de todos os usuários é resetado para o perfil PADRÃO.'
                    )
                }}
            </p>

            <p>
                {{ __('Já ao se tornar uma lotação administrável, não há alteração nos perfis.') }}
            </p>
        </Alerta>

        <Container class="space-y-3">
            <div class="flex flex-col space-y-3 md:flex-row md:items-start md:justify-end">
                <Preferencia>
                    <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />

                    <CheckBox v-model:checked="elementosVisiveis.lotacao" :label="__('Lotação')" />

                    <CheckBox v-model:checked="elementosVisiveis.sigla" :label="__('Sigla')" />

                    <CheckBox
                        v-model:checked="elementosVisiveis.administravel"
                        :label="__('Administrável')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.lotacaoPai"
                        :label="__('Lotação pai')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.usuarios"
                        :label="__('Qtd usuários')"
                    />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" fixo />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.lotacao"
                        :ordenacao="ordenacoes.nome"
                        :texto="__('Lotação')"
                        @ordenar="(direcao) => mudarOrdenacao('nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.sigla"
                        :ordenacao="ordenacoes.sigla"
                        :texto="__('Sigla')"
                        @ordenar="(direcao) => mudarOrdenacao('sigla', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.administravel"
                        :ordenacao="ordenacoes.administravel"
                        :texto="__('Administrável')"
                        @ordenar="(direcao) => mudarOrdenacao('administravel', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.lotacaoPai"
                        :ordenacao="ordenacoes.lotacao_pai_sigla"
                        :texto="__('Lotação pai')"
                        @ordenar="(direcao) => mudarOrdenacao('lotacao_pai_sigla', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.usuarios"
                        :ordenacao="ordenacoes.usuarios_count"
                        :texto="__('Qtd usuários')"
                        @ordenar="(direcao) => mudarOrdenacao('usuarios_count', direcao)"
                    />
                </template>

                <template #body>
                    <template v-if="lotacoes.data.length">
                        <Row v-for="lotacao in lotacoes.data" :key="lotacao.id">
                            <Cell v-show="elementosVisiveis.acao" class="w-10" fixo>
                                <div class="flex space-x-3">
                                    <ButtonIcone
                                        v-if="lotacao.links.update"
                                        :especie="lotacao.administravel ? 'padrao' : 'perigo'"
                                        :icone="
                                            lotacao.administravel
                                                ? 'hand-thumbs-up'
                                                : 'hand-thumbs-down'
                                        "
                                        @click="administravel(lotacao.links.update)"
                                        dusk="submit"
                                        type="submit"
                                    />
                                </div>
                            </Cell>

                            <Cell v-show="elementosVisiveis.lotacao">{{ lotacao.nome }}</Cell>

                            <Cell v-show="elementosVisiveis.sigla">{{ lotacao.sigla }}</Cell>

                            <Cell v-show="elementosVisiveis.administravel">
                                {{ eAdministravel(lotacao) }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.lotacaoPai">
                                <span>{{ lotacao.lotacaoPai?.sigla }}</span>

                                <Tooltip
                                    v-if="lotacao.lotacaoPai?.nome"
                                    :texto="lotacao.lotacaoPai?.nome"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.usuarios">
                                {{ lotacao.usuarios_count }}
                            </Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell :colspan="colspan">{{ __('Nenhum registro encontrado!') }}</Cell>
                        </Row>
                    </template>
                </template>
            </Tabela>

            <Paginacao v-if="lotacoes.meta.last_page > 1" :meta="lotacoes.meta" />
        </Container>
    </Pagina>
</template>
