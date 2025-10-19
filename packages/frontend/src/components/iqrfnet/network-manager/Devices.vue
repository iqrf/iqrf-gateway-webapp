<template>
	<ICard>
		<template #title>
			{{ $t('components.iqrfnet.network-manager.devices.title') }}
		</template>
		<template #titleActions>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				@click='getBondedDevices()'
			/>
		</template>
		<v-table class='text-center'>
			<tbody>
				<tr>
					<td>
						<v-icon
							color='info'
							size='large'
							:icon='mdiHomeOutline'
						/>
						{{ $t('components.iqrfnet.network-manager.devices.states.coordinator') }}
					</td>
					<td>
						<v-icon
							color='error'
							size='large'
							:icon='mdiClose'
						/>
						{{ $t('components.iqrfnet.network-manager.devices.states.unbonded') }}
					</td>
				</tr>
				<tr>
					<td>
						<v-icon
							color='info'
							size='large'
							:icon='mdiCheck'
						/>
						{{ $t('components.iqrfnet.network-manager.devices.states.bonded') }}
					</td>
					<td>
						<v-icon
							color='info'
							size='large'
							:icon='mdiSignalCellularOutline'
						/>
						{{ $t('components.iqrfnet.network-manager.devices.states.discovered') }}
					</td>
				</tr>
				<tr>
					<td>
						<v-icon
							color='success'
							size='large'
							:icon='mdiCheck'
						/>
						{{ $t('components.iqrfnet.network-manager.devices.states.onlineBonded') }}
					</td>
					<td>
						<v-icon
							color='success'
							size='large'
							:icon='mdiSignalCellularOutline'
						/>
						{{ $t('components.iqrfnet.network-manager.devices.states.onlineDiscovered') }}
					</td>
				</tr>
			</tbody>
		</v-table>
		<v-btn-group
			class='d-flex'
			rounded
			divided
		>
			<IActionBtn
				class='flex-fill text-wrap'
				:action='Action.Custom'
				color='info'
				:icon='mdiLightbulbOnOutline'
				flat
				:text='$t("components.iqrfnet.network-manager.devices.actions.indicate")'
				:loading='componentState === ComponentState.Action && actionType === ActionType.INDICATE'
				:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && actionType !== ActionType.INDICATE)'
				@click='indicateCoordinator()'
			/>
			<IActionBtn
				class='flex-fill text-wrap'
				:action='Action.Custom'
				color='primary'
				:icon='mdiRadar'
				flat
				:text='$t("components.iqrfnet.network-manager.devices.actions.ping")'
				:loading='componentState === ComponentState.Action && actionType === ActionType.PING'
				:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && actionType !== ActionType.PING)'
				@click='ping()'
			/>
			<IActionBtn
				class='flex-fill text-wrap'
				:action='Action.Restart'
				flat
				:text='$t("components.iqrfnet.network-manager.devices.actions.restart")'
				:loading='componentState === ComponentState.Action && actionType === ActionType.RESTART'
				:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && actionType !== ActionType.RESTART)'
				@click='restart()'
			/>
		</v-btn-group>
		<v-table v-if='devices.length > 0'>
			<tbody>
				<tr>
					<th />
					<th
						v-for='num of Array(10).keys()'
						:key='`device-row-col-${num}`'
					>
						{{ num }}
					</th>
				</tr>
				<tr
					v-for='row of Array(24).keys()'
					:key='`device-row-${row}`'
				>
					<th>{{ `${row}0` }}</th>
					<td
						v-for='col of Array(10).keys()'
						:key='`device-row-${row}-col-${col}`'
					>
						<DeviceIcon :device='devices[row * 10 + col]' />
					</td>
				</tr>
			</tbody>
		</v-table>
	</ICard>
	<RestartFailed v-model='failedRestart' />
</template>

<script lang='ts' setup>
import { EmbedCoordinatorMessages, EmbedOsMessages, IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { CoordinatorService } from '@iqrf/iqrf-gateway-daemon-utils/services/embed';
import { NetworkService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { PingResultItem } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard } from '@iqrf/iqrf-vue-ui';
import { mdiCheck, mdiClose, mdiHomeOutline, mdiLightbulbOnOutline, mdiRadar, mdiSignalCellularOutline } from '@mdi/js';
import { onBeforeUnmount, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DeviceIcon from '@/components/iqrfnet/network-manager/DeviceIcon.vue';
import RestartFailed from '@/components/iqrfnet/network-manager/RestartFailed.vue';
import Device from '@/helpers/device';
import { useDaemonStore } from '@/store/daemonSocket';

enum ActionType {
	INDICATE = 0,
	PING = 1,
	RESTART = 2,
}


const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const devices: Ref<Device[]> = ref(Array.from({ length: 240 }, (_, i) => new Device(i, i === 0)));
const msgId: Ref<string | null> = ref(null);
const actionType: Ref<ActionType | null> = ref(null);
const failedRestart: Ref<number[]> = ref([]);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			switch (rsp.mType) {
				case EmbedCoordinatorMessages.BondedDevices:
					handleBondedDevices(rsp);
					break;
				case EmbedCoordinatorMessages.DiscoveredDevices:
					handleDiscoveredDevices(rsp);
					break;
				case IqmeshServiceMessages.Ping:
					handlePing(rsp);
					break;
				case IqmeshServiceMessages.Restart:
					handleRestart(rsp);
					break;
				case EmbedOsMessages.Batch:
					handleIndicate(rsp);
					break;
			}
		});
	}
});

async function getBondedDevices(): Promise<void> {
	actionType.value = null;
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.network-manager.devices.messages.bonded.timeout'),
		() => {
			msgId.value = null;
			componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		CoordinatorService.bondedDevices(
			{ addr: 0 },
			opts,
		),
	);
}

function handleBondedDevices(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.devices.messages.bonded.failed'),
		);
		componentState.value = ComponentState.Ready;
		return;
	}
	for (let i = 0; i < devices.value.length; ++i) {
		devices.value[i].bonded = false;
	}
	const bonded: number[] = rsp.data.rsp.result.bondedDevices;
	if (bonded.length === 0) {
		componentState.value = ComponentState.Ready;
		return;
	}
	for (const addr of bonded) {
		devices.value[addr].bonded = true;
	}
	getDiscoveredDevices();
}

async function getDiscoveredDevices(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.network-manager.devices.messages.discovered.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Ready;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		CoordinatorService.discoveredDevices(
			{ addr: 0 },
			opts,
		),
	);
}

function handleDiscoveredDevices(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.devices.messages.discovered.failed'),
		);
		componentState.value = ComponentState.Ready;
		return;
	}
	for (let i = 0; i < devices.value.length; ++i) {
		devices.value[i].discovered = false;
	}
	const discovered: number[] = rsp.data.rsp.result.discoveredDevices;
	for (const addr of discovered) {
		devices.value[addr].discovered = true;
	}
	ping();
}

async function ping(): Promise<void> {
	if (componentState.value === ComponentState.Ready) {
		componentState.value = ComponentState.Action;
		actionType.value = ActionType.PING;
	}
	const opts = new DaemonMessageOptions(
		null,
		90_000,
		i18n.t('components.iqrfnet.network-manager.devices.messages.ping.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Ready;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		NetworkService.ping(
			{},
			{ hwpId: 0xFF_FF },
			opts,
		),
	);
}

function handlePing(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		const results = rsp.data.rsp.pingResult;
		for (const device of results) {
			devices.value[device.address].online = device.result;
		}
	} else if (rsp.data.status === 1_003) {
		toast.error(
			i18n.t('common.messages.noDevices'),
		);
	} else {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.devices.messages.ping.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

async function restart(): Promise<void> {
	if (componentState.value === ComponentState.Ready) {
		componentState.value = ComponentState.Action;
		actionType.value = ActionType.RESTART;
	}
	const opts = new DaemonMessageOptions(
		null,
		90_000,
		i18n.t('components.iqrfnet.network-manager.devices.messages.restart.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Ready;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		NetworkService.restart(
			{},
			{ hwpId: 0xFF_FF },
			opts,
		),
	);
}

function handleRestart(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		if (rsp.data.rsp.inaccessibleNodesNr === 0) {
			toast.success(
				i18n.t('components.iqrfnet.network-manager.devices.messages.restart.success'),
			);
		} else {
			const results = rsp.data.rsp.restartResult;
			failedRestart.value = results.filter((e: PingResultItem) => !e.result).map((device: PingResultItem) => device.address);
		}
	} else if (rsp.data.status === 1_003) {
		toast.error(
			i18n.t('common.messages.noDevices'),
		);
	} else {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.devices.messages.restart.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

async function indicateCoordinator(): Promise<void> {
	componentState.value = ComponentState.Action;
	actionType.value = ActionType.INDICATE;
	const opts = new DaemonMessageOptions(
		{
			mType: EmbedOsMessages.Batch,
			data: {
				msgId: 'coord_indicate',
				req: {
					nAdr: 0,
					param: {
						requests: [
							{
								hwpid: 'ffff',
								pnum: '06',
								pcmd: '03',
							},
							{
								hwpid: 'ffff',
								pnum: '07',
								pcmd: '03',
							},
						],
					},
				},
				returnVerbose: true,
			},
		},
		90_000,
		i18n.t('components.iqrfnet.network-manager.devices.messages.indicate.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Ready;
		},
	);
	msgId.value = await daemonStore.sendMessage(opts);
}

function handleIndicate(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		toast.success(
			i18n.t('components.iqrfnet.network-manager.devices.messages.indicate.success'),
		);
	} else {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.devices.messages.indicate.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

defineExpose({
	getBondedDevices,
});

onMounted(() => {
	getBondedDevices();
});

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});
</script>
