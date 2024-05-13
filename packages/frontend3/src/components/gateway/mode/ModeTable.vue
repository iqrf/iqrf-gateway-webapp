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
			{{ $t('pages.gateway.mode.title') }}
		</template>
		<v-table>
			<tbody>
				<tr>
					<td>
						<strong>{{ $t('components.gateway.mode.current') }}</strong>
					</td>
					<td>
						<v-menu>
							<template #activator='{ props }'>
								<v-btn
									v-bind='props'
									color='primary'
									size='small'
									:disabled='currentMode === DaemonMode.Unknown'
								>
									{{ $t(`components.gateway.mode.modes.${currentMode}`) }}
									<v-icon :icon='mdiMenuDown' />
								</v-btn>
							</template>
							<v-list density='compact'>
								<v-list-item
									v-for='(mode) in modeOptions'
									:key='mode'
									@click='setMode(mode)'
								>
									{{ $t(`components.gateway.mode.modes.${mode}`) }}
								</v-list-item>
							</v-list>
						</v-menu>
					</td>
				</tr>
				<tr>
					<td>
						<strong>{{ $t('components.gateway.mode.startup') }}</strong>
					</td>
					<td>
						<v-menu>
							<template #activator='{ props }'>
								<v-btn
									v-bind='props'
									color='primary'
									size='small'
								>
									{{ $t(`components.gateway.mode.modes.${startupMode}`) }}
									<v-icon :icon='mdiMenuDown' />
								</v-btn>
							</template>
							<v-list density='compact'>
								<v-list-item
									v-for='(mode) in startupModeOptions'
									:key='mode'
									@click='setStartupMode(mode)'
								>
									{{ $t(`components.gateway.mode.modes.${mode}`) }}
								</v-list-item>
							</v-list>
						</v-menu>
					</td>
				</tr>
			</tbody>
		</v-table>
	</Card>
</template>

<script lang='ts' setup>
import { DaemonMode, ManagementMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { ManagementService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { type DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	type IqrfGatewayDaemonComponent,
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonIdeCounterpart,
	IqrfGatewayDaemonIdeCounterpartMode,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiMenuDown } from '@mdi/js';
import { onMounted, type Ref, ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { useMonitorStore } from '@/store/monitorSocket';

const i18n = useI18n();
const daemonStore = useDaemonStore();
const monitorStore = useMonitorStore();
const daemonConfigService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const msgId: Ref<string | null> = ref(null);
const modeOptions = [
	DaemonMode.Forwarding,
	DaemonMode.Operational,
	DaemonMode.Service,
];
const startupModeOptions = [
	IqrfGatewayDaemonIdeCounterpartMode.Forwarding,
	IqrfGatewayDaemonIdeCounterpartMode.Operational,
	IqrfGatewayDaemonIdeCounterpartMode.Service,
];
const currentMode: Ref<DaemonMode> = ref(DaemonMode.Unknown);
const startupMode: Ref<IqrfGatewayDaemonIdeCounterpartMode> = ref(IqrfGatewayDaemonIdeCounterpartMode.Operational);
const componentName = IqrfGatewayDaemonComponentName.IqrfIdeCounterpart;
const instance: Ref<IqrfGatewayDaemonIdeCounterpart | null> = ref(null);

daemonStore.$onAction(
	({ name, after }) => {
		if (name === 'onMessage') {
			after((rsp: DaemonApiResponse) => {
				if (rsp.data.msgId !== msgId.value) {
					return;
				}
				daemonStore.removeMessage(msgId.value);
				if (rsp.mType === ManagementMessages.Mode) {
					handleSetMode(rsp);
				}
			});
		}
	},
);

watchEffect(() => {
	const mode = monitorStore.getMode;
	if (mode !== currentMode.value) {
		currentMode.value = mode;
	}
});

function setMode(mode: DaemonMode): void {
	const options = new DaemonMessageOptions(
		null,
		5000,
		'components.gateway.mode.messages.setTimeout',
		() => {msgId.value = null;},
	);
	daemonStore.sendMessage(
		ManagementService.setMode(mode, options),
	)
		.then((val: string) => msgId.value = val);
}

function handleSetMode(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.gateway.mode.message.setFailed'),
		);
		return;
	}
	monitorStore.setMode(rsp.data.rsp.operMode);
	toast.success(
		i18n.t('components.gateway.mode.messages.setSuccess', { mode: rsp.data.rsp.operMode }),
	);
}

async function getStartupMode(): Promise<void> {
	daemonConfigService.getComponent(componentName)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfIdeCounterpart>) => {
			const inst: IqrfGatewayDaemonIdeCounterpart = response.instances[0];
			if (inst.operMode === undefined) {
				inst.operMode = IqrfGatewayDaemonIdeCounterpartMode.Operational;
			}
			instance.value = inst;
			startupMode.value = inst.operMode;
		});
}

function setStartupMode(mode: IqrfGatewayDaemonIdeCounterpartMode): void {
	if (instance.value === null || instance.value.operMode === mode) {
		return;
	}
	const configuration: IqrfGatewayDaemonIdeCounterpart = {
		...instance.value,
		operMode: mode,
	};
	daemonConfigService.updateInstance(componentName, configuration.instance, configuration)
		.then(() => {
			getStartupMode().then(() => {
				toast.success('TODO SUCCESS MESSAGE');
			});
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

onMounted(() => {
	getStartupMode();
});
</script>
