<template>
	<Card>
		<template #title>
			{{ $t(`components.configuration.daemon.interfaces.${interfaceType}.devices`) }}
		</template>
		<template #titleActions>
			<v-tooltip
				location='bottom'
			>
				<template #activator='{ props }'>
					<v-btn
						v-bind='props'
						color='white'
						:icon='mdiReload'
						@click='getPorts'
					/>
				</template>
				{{ $t('common.buttons.reload') }}
			</v-tooltip>
		</template>
		<DataTable
			:headers='headers'
			:items='ports'
			:loading='componentState === ComponentState.Loading'
			:hover='true'
			:dense='true'
			:items-per-page='5'
		>
			<template #item.interface='{ item }'>
				{{ item }}
			</template>
			<template #item.actions='{ item }'>
				<v-icon
					color='success'
					size='large'
					class='me-2'
					:icon='mdiCheckboxMarkedOutline'
					@click='applyInterface(item)'
				/>
				<v-tooltip
					activator='parent'
					location='bottom'
				>
					{{ $t('components.configuration.daemon.interfaces.apply') }}
				</v-tooltip>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type InterfacePortsService } from '@iqrf/iqrf-gateway-webapp-client/services/Iqrf';
import { type IqrfInterfaceType } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { mdiCheckboxMarkedOutline, mdiReload } from '@mdi/js';
import { type PropType, type Ref, ref } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentProps = defineProps({
	interfaceType: {
		type: String as PropType<IqrfInterfaceType>,
		required: true,
	},
});
const emit = defineEmits(['apply']);
const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: InterfacePortsService = useApiClient().getIqrfServices().getInterfacePortsService();
const headers = [
	{ key: 'interface', title: i18n.t('components.configuration.daemon.interfaces.interface') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const ports: Ref<string[]> = ref([]);

function getPorts(): void {
	componentState.value = ComponentState.Loading;
	service.getInterfacePorts(componentProps.interfaceType)
		.then((response: string[]): void => {
			ports.value = response;
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO GET ERROR HANDLING'));
}

function applyInterface(iface: string): void {
	emit('apply', iface);
}

onMounted(() => {
	getPorts();
});
</script>
