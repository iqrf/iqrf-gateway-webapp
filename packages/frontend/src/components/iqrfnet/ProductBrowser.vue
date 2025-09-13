<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<ModalWindow
		v-model='show'
	>
		<template #activator='{ props }'>
			<v-list-item
				v-if='listActivator'
				v-bind='props'
				@click='show = true'
			>
				{{ $t('components.iqrfnet.products.button') }}
			</v-list-item>
			<v-btn
				v-else
				v-bind='props'
				color='primary'
				@click='show = true'
			>
				{{ $t('components.iqrfnet.products.button') }}
			</v-btn>
		</template>
		<ICard>
			<template #title>
				{{ $t('components.iqrfnet.products.title') }}
			</template>
			<DataTable
				:headers='headers'
				:items='products'
				:search='search'
				:hover='true'
				:dense='true'
			>
				<template #top>
					<TextInput
						v-model='search'
						:prepend-inner-icon='mdiMagnify'
						density='compact'
						single-line
						flat
						hide-details
						variant='solo-filled'
					/>
				</template>
				<template #item.actions='{ item }'>
					<v-btn
						color='success'
						size='small'
						@click='applyProduct(item)'
					>
						<v-icon :icon='mdiImport' />
					</v-btn>
				</template>
			</DataTable>
			<template #actions>
				<v-spacer />
				<ICardActionBtn
					:action='Action.Cancel'
					@click='close'
				/>
			</template>
		</ICard>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type ProductService } from '@iqrf/iqrf-repository-client/services';
import { type Product } from '@iqrf/iqrf-repository-client/types';
import { Action, ICard, ICardActionBtn } from '@iqrf/iqrf-vue-ui';
import { mdiImport, mdiMagnify } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import DataTable from '@/components/layout/data-table/DataTable.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { useRepositoryClient } from '@/services/RepositoryClient';

defineProps({
	listActivator: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const emit = defineEmits(['apply', 'close']);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
let service: ProductService;
const headers = [
	{ key: 'companyName', title: i18n.t('components.iqrfnet.products.manufacturer') },
	{ key: 'name', title: i18n.t('components.iqrfnet.products.name') },
	{ key: 'hwpid', title: i18n.t('components.iqrfnet.products.hwpid') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const products: Ref<Product[]> = ref([]);
const search: Ref<string> = ref('');

async function list(): Promise<void> {
	products.value = await service.list();
}

function applyProduct(product: Product): void {
	emit('apply', product);
	close();
}

function close(): void {
	emit('close');
	show.value = false;
}

onMounted(async () => {
	const client = await useRepositoryClient();
	service = client.getProductService();
	await list();
});
</script>
