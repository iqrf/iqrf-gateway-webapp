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
	<ICard :width='cardWidth'>
		<template #title>
			{{ $t(`components.config.daemon.interfaces.${interfaceType}.devices`) }}
		</template>
		<template #titleActions>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:tooltip='$t("components.config.daemon.interfaces.cdc.devices.actions.reload")'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				@click='getPorts()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='ports'
			:loading='componentState === ComponentState.Loading'
			:hover='true'
			:dense='true'
			:items-per-page='5'
			disable-column-filtering
		>
			<template #item.interface='{ item }'>
				{{ item }}
			</template>
			<template #item.actions='{ item }'>
				<IDataTableAction
					:action='Action.Apply'
					:tooltip='$t("components.config.daemon.interfaces.cdc.devices.actions.apply")'
					@click='applyInterface(item)'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type InterfacePortsService } from '@iqrf/iqrf-gateway-webapp-client/services/Iqrf';
import { type IqrfInterfaceType } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	IDataTableAction,
} from '@iqrf/iqrf-vue-ui';
import { computed, onBeforeMount, type PropType, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify/lib/composables/display.mjs';

import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps({
	interfaceType: {
		type: String as PropType<IqrfInterfaceType>,
		required: true,
	},
});
const emit = defineEmits(['apply']);
const display = useDisplay();
const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: InterfacePortsService = useApiClient().getIqrfServices().getInterfacePortsService();
const headers = [
	{ key: 'interface', title: i18n.t('components.config.daemon.interfaces.interface') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const ports: Ref<string[]> = ref([]);

const cardWidth = computed(() => {
	if (display.lgAndUp.value) {
		return '50vw';
	}
	if (display.md.value) {
		return '75vw';
	}
	return '100vw';
});

async function getPorts(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		ports.value = await service.getInterfacePorts(componentProps.interfaceType);
	} catch {
		toast.error('TODO GET ERROR HANDLING');
	}
	componentState.value = ComponentState.Ready;
}

function applyInterface(iface: string): void {
	emit('apply', iface);
}

onBeforeMount(() => {
	getPorts();
});
</script>
