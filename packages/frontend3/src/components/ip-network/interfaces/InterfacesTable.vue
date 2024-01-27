<template>
	<Card header-color='primary'>
		<template #title>
			{{ $t(`components.ipNetwork.interfaces.titles.${type}`) }}
		</template>
		<template #titleActions>
			<v-btn
				color='primary'
				:icon='mdiRefresh'
				variant='elevated'
				@click='fetchData'
			/>
		</template>
		<v-skeleton-loader
			class='input-skeleton-loader'
			:loading='componentState === ComponentState.Loading'
			type='table-heading, table-row-divider@2, table-row'
		>
			<v-responsive>
				<DataTable
					:items='interfaces'
					:headers='headers'
					hide-pagination
					dense
				>
					<template #item.state='{item}'>
						<InterfaceStateBadge :state='item.state' />
					</template>
				</DataTable>
			</v-responsive>
		</v-skeleton-loader>
	</Card>
</template>

<script setup lang='ts'>
import {
	type NetworkInterface,
	type NetworkInterfaceType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkInterface';
import { mdiRefresh } from '@mdi/js';
import { onBeforeMount, type PropType, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import InterfaceStateBadge
	from '@/components/ip-network/interfaces/InterfaceStateBadge.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentProps = defineProps({
	type: {
		type: [String, null] as PropType<NetworkInterfaceType | null>,
		default: null,
		required: false,
	},
});
const i18n = useI18n();
const service = useApiClient().getNetworkServices().getNetworkInterfaceService();

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const interfaces: Ref<NetworkInterface[]> = ref([]);
const headers = [
	{
		key: 'name',
		title: i18n.t('components.ipNetwork.interfaces.columns.name'),
	},
	{
		key: 'manufacturer',
		title: i18n.t('components.ipNetwork.interfaces.columns.manufacturer'),
	},
	{
		key: 'model',
		title: i18n.t('components.ipNetwork.interfaces.columns.model'),
	},
	{
		key: 'macAddress',
		title: i18n.t('components.ipNetwork.interfaces.columns.macAddress'),
	},
	{
		align: 'end',
		key: 'state',
		title: i18n.t('components.ipNetwork.interfaces.columns.state'),
	},
];

/**
 * Fetches network interfaces
 */
function fetchData() {
	componentState.value = ComponentState.Loading;
	service.list(componentProps.type)
		.then((response: NetworkInterface[]): NetworkInterface[] => {
			interfaces.value = response;
			componentState.value = ComponentState.Ready;
			return response;
		});
}

onBeforeMount(() => {
	fetchData();
});
</script>
