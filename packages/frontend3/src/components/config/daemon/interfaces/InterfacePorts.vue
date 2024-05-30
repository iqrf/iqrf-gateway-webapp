<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<Card>
		<template #title>
			{{ $t(`components.configuration.daemon.interfaces.${interfaceType}.devices`) }}
		</template>
		<template #titleActions>
			<CardTitleActionBtn
				:action='Action.Reload'
				@click='getPorts'
			/>
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
				<DataTableAction
					:action='Action.Apply'
					:tooltip='$t("components.configuration.daemon.interfaces.apply")'
					@click='applyInterface(item)'
				/>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type InterfacePortsService } from '@iqrf/iqrf-gateway-webapp-client/services/Iqrf';
import { type IqrfInterfaceType } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { type PropType, type Ref, ref } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
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
