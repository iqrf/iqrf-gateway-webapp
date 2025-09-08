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
		v-if='action === Action.Disable'
		v-model='showDialog'
	>
		<template #activator='scope'>
			<DataTableAction
				v-bind='scope.props'
				color='red'
				:icon='mdiLinkVariantOff'
				:tooltip='$t("components.ipNetwork.connections.actions.disconnect")'
			/>
		</template>
		<Card header-color='red'>
			<template #title>
				{{ $t('components.ipNetwork.connections.disconnect.title') }}
			</template>
			{{ $t('components.ipNetwork.connections.disconnect.prompt', { name: connection.name }) }}
			<template #actions>
				<CardActionBtn
					color='red'
					:icon='mdiLinkVariantOff'
					:loading='componentState === ComponentState.Saving'
					:text='$t("components.ipNetwork.connections.actions.disconnect")'
					@click='disconnect()'
				/>
				<v-spacer />
				<CardActionBtn
					:action='Action.Cancel'
					:disabled='componentState === ComponentState.Saving'
					@click='close()'
				/>
			</template>
		</Card>
	</ModalWindow>
	<DataTableAction
		v-else-if='action === Action.Enable'
		color='green'
		:icon='mdiLinkVariant'
		:loading='componentState === ComponentState.Saving'
		:tooltip='$t("components.ipNetwork.connections.actions.connect")'
		@click='connect()'
	/>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Network';
import {
	type NetworkConnectionListEntry,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { mdiLinkVariant, mdiLinkVariantOff } from '@mdi/js';
import { computed, ComputedRef, type PropType, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import DataTableAction
	from '@/components/layout/data-table/DataTableAction.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

/// Component props
const componentProps = defineProps({
	/// Network connection
	connection: {
		type: Object as PropType<NetworkConnectionListEntry>,
		required: true,
	},
});
/// Component emits
const emits = defineEmits(['change']);
/// Action
const action: ComputedRef<Action> = computed((): Action =>
	componentProps.connection.isActive ? Action.Disable : Action.Enable,
);
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Show dialog window
const showDialog: Ref<boolean> = ref(false);
/// Internationalization instance
const i18n = useI18n();
/// Network connection service
const service: NetworkConnectionService = useApiClient().getNetworkServices().getNetworkConnectionService();

/**
 * Close dialog window
 */
function close(): void {
	showDialog.value = false;
}

/**
 * Connects network connection
 */
async function connect(): Promise<void> {
	if (componentProps.connection === undefined) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const translationParams = { name: componentProps.connection.name };
	try {
		await service.connect(componentProps.connection.uuid);
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.ipNetwork.connections.connect.messages.success', translationParams),
		);
		emits('change');
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(
			i18n.t('components.ipNetwork.connections.connect.messages.failure', translationParams),
		);
	}
}

/**
 * Disconnects network connection
 */
async function disconnect(): Promise<void> {
	if (componentProps.connection === undefined) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const translationParams = { name: componentProps.connection.name };
	try {
		await service.disconnect(componentProps.connection.uuid);
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.ipNetwork.connections.disconnect.messages.success', translationParams),
		);
		close();
		emits('change');
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(
			i18n.t('components.ipNetwork.connections.disconnect.messages.failure', translationParams),
		);
	}
}
</script>
