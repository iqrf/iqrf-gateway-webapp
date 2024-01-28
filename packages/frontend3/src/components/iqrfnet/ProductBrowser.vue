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
		<Card>
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
				<v-btn
					color='grey-darken-2'
					variant='elevated'
					@click='close'
				>
					{{ $t('common.buttons.close') }}
				</v-btn>
			</template>
		</Card>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type Product, type ProductService } from '@iqrf/iqrf-repository-client';
import { mdiImport, mdiMagnify } from '@mdi/js';
import { onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import TextInput from '@/components/TextInput.vue';
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
	{key: 'companyName', title: i18n.t('components.iqrfnet.products.manufacturer')},
	{key: 'name', title: i18n.t('components.iqrfnet.products.name')},
	{key: 'hwpid', title: i18n.t('components.iqrfnet.products.hwpid')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
];
const products: Ref<Product[]> = ref([]);
const search: Ref<string> = ref('');

function list(): void {
	service.list()
		.then((data: Product[]) => {
			products.value = data;
		});
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
	list();
});
</script>
