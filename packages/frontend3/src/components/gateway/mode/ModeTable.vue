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
import { Ref, ref } from 'vue';
import Card from '@/components/Card.vue';
import { IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { IqrfGatewayDaemonIdeCounterpart, IqrfGatewayDaemonIdeCounterpartMode } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { DaemonMode } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { mdiMenuDown } from '@mdi/js';
import { useDaemonStore } from '@/store/daemonSocket';
import { onMounted } from 'vue';
import { useApiClient } from '@/services/ApiClient';
import { AxiosResponse } from 'axios';

const daemonStore = useDaemonStore();
const daemonConfigService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

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
const componentName: string = 'iqrf::IdeCounterpart';
const instance: Ref<IqrfGatewayDaemonIdeCounterpart | null> = ref(null);

onMounted(() => {
	getMode();
	getStartupMode();
});

function getMode(): void {

}

async function getStartupMode(): Promise<void> {
	daemonConfigService.getComponent(componentName)
		.then((rsp: AxiosResponse) => {
			const inst: IqrfGatewayDaemonIdeCounterpart = (rsp.data.instances as IqrfGatewayDaemonIdeCounterpart[])[0];
			if (inst.operMode === undefined) {
				inst.operMode = IqrfGatewayDaemonIdeCounterpartMode.Operational;
			}
			instance.value = inst;
			startupMode.value = instance.value.operMode as IqrfGatewayDaemonIdeCounterpartMode;
		});
}

function setStartupMode(mode: IqrfGatewayDaemonIdeCounterpartMode): void {
	if (instance.value === null || instance.value.operMode === mode) {
		return;
	}
	const configuration = {...instance.value};
	configuration.operMode = mode;
	daemonConfigService.updateInstance(componentName, configuration.instance, configuration)
		.then(() => {
			getStartupMode().then(() => {

			});
		});
	// TODO ERROR HANDLING
}
</script>
