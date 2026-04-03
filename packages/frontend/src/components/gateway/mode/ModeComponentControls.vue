<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
	<ICard>
		<template #title>
			{{ $t('components.gateway.mode.feature.title') }}
		</template>
		<template #titleActions>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				:tooltip='$t("components.gateway.mode.feature.actions.refresh")'
				@click='getStatus()'
			/>
		</template>
		<v-alert
			:title='$t("components.gateway.mode.feature.status")'
			:color='statusColor'
			:icon='statusIcon'
			:text='statusMessage'
			variant='tonal'
		/>
		<template #actions>
			<IActionBtn
				v-if='enabled'
				color='primary'
				:icon='mdiStopCircleOutline'
				:loading='componentState === ComponentState.Action'
				:disabled='enabled === null || componentState !== ComponentState.Idle'
				:text='$t("common.buttons.disable")'
				@click='changeStatus(false)'
			/>
			<IActionBtn
				v-if='enabled === false'
				color='primary'
				:icon='mdiPlayCircleOutline'
				:loading='componentState === ComponentState.Action'
				:disabled='enabled === null || componentState !== ComponentState.Idle'
				:text='$t("common.buttons.enable")'
				@click='changeStatus(true)'
			/>
		</template>
	</ICard>
</template>

<script setup lang='ts'>
import { ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { IqrfGatewayDaemonComponentName } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
} from '@iqrf/iqrf-vue-ui';
import {
	mdiAlertCircleOutline,
	mdiDownload,
	mdiPlayCircleOutline,
	mdiStopCircleOutline,
} from '@mdi/js';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits<{
	fetched: [status: boolean];
}>();
const componentState = ref<ComponentState>(ComponentState.Created);
const i18n = useI18n();
const daemonConfigService: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();
const servicesService: ServiceService = useApiClient()
	.getServiceService();
const enabled = ref<boolean | null>(null);
const serviceName = 'iqrf-gateway-daemon';

const statusColor = computed<string>(() => {
	if (componentState.value === ComponentState.Loading || componentState.value === ComponentState.Reloading) {
		return 'grey';
	}
	if (enabled.value === null) {
		return 'warning';
	}
	if (enabled.value) {
		return 'success';
	}
	return 'red';
});

const statusIcon = computed<string>(() => {
	if (componentState.value === ComponentState.Loading || componentState.value === ComponentState.Reloading) {
		return mdiDownload;
	}
	if (enabled.value === null) {
		return mdiAlertCircleOutline;
	}
	if (enabled.value) {
		return mdiPlayCircleOutline;
	}
	return mdiStopCircleOutline;
});

const statusMessage = computed<string>(() => {
	if (componentState.value === ComponentState.Loading || componentState.value === ComponentState.Reloading) {
		return i18n.t('components.gateway.mode.feature.statuses.fetching');
	}
	if (enabled.value === null) {
		return i18n.t('components.gateway.mode.feature.statuses.unknown');
	}
	if (enabled.value) {
		return i18n.t('components.gateway.mode.feature.statuses.enabled');
	}
	return i18n.t('components.gateway.mode.feature.statuses.disabled');
});

async function getStatus(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const data = await daemonConfigService.getConfig();
		enabled.value = data.components
			.filter(c => c.name === IqrfGatewayDaemonComponentName.IqrfIdeCounterpart ||
				c.name === IqrfGatewayDaemonComponentName.IqrfUdpMessaging)
			.map(c => c.enabled)
			.reduce((acc, val) => acc && val, true);
		emit('fetched', enabled.value);
		componentState.value = ComponentState.Idle;
	} catch {
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Idle;
		toast.error(
			i18n.t('components.gateway.mode.feature.messages.get.failed'),
		);
	}
}

async function changeStatus(status: boolean): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await daemonConfigService.updateEnabledComponents(
			[
				{
					name: IqrfGatewayDaemonComponentName.IqrfIdeCounterpart,
					enabled: status,
				},
				{
					name: IqrfGatewayDaemonComponentName.IqrfUdpMessaging,
					enabled: status,
				},
			],
		);
	} catch {
		if (status) {
			toast.error(
				i18n.t('components.gateway.mode.feature.messages.enable.failed'),
			);
		} else {
			toast.error(
				i18n.t('components.gateway.mode.feature.messages.disable.failed'),
			);
		}
		componentState.value = ComponentState.Idle;
		return;
	}
	try {
		await servicesService.restart(serviceName);
		if (status) {
			toast.success(
				i18n.t('components.gateway.mode.feature.messages.restartEnable.success'),
			);
		} else {
			toast.success(
				i18n.t('components.gateway.mode.feature.messages.restartDisable.success'),
			);
		}
		enabled.value = status;
		emit('fetched', status);
	} catch {
		if (status) {
			toast.error(
				i18n.t('components.gateway.mode.feature.messages.restartEnable.failed'),
			);
		} else {
			toast.error(
				i18n.t('components.gateway.mode.feature.messages.restartDisable.failed'),
			);
		}
	}
	componentState.value = ComponentState.Idle;
}

onMounted(() => {
	getStatus();
});
</script>
