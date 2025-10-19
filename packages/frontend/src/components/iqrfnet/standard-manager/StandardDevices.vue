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
	<ICard>
		<template #title>
			{{ $t('components.iqrfnet.standard-manager.standard-devices.title') }}
		</template>
		<template #titleActions>
			<EnumerationDialog
				:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				@finished='getDevices()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				@click='getDevices()'
			/>
			<DatabaseResetDialog
				:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				@reset='getDevices()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='devices'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
			item-value='address'
			:hover='true'
			:dense='true'
			disable-column-filtering
		>
			<template #item.address='{ item }'>
				{{ item.getAddress() }}
			</template>
			<template #item.product='{ item }'>
				{{ item.getProductName() }}
			</template>
			<template #item.os='{ item }'>
				{{ item.getOs() }}
			</template>
			<template #item.dpa='{ item }'>
				{{ item.getDpa() }}
			</template>
			<template #item.status='{ item }'>
				<v-icon
					:color='item.getIconColor()'
					:icon='item.getIcon()'
				/>
			</template>
			<template #item.sensor='{ item }'>
				<IBooleanIcon
					:value='item.hasSensor()'
				/>
			</template>
			<template #item.binout='{ item }'>
				<IBooleanIcon
					v-if='!item.hasBinout()'
					:value='false'
				/>
				<span v-else>{{ item.getBinouts() }}</span>
			</template>
			<template #item.light='{ item }'>
				<IBooleanIcon
					:value='item.hasLight()'
				/>
			</template>
			<template #item.actions='{ internalItem, toggleExpand, isExpanded }'>
				<IDataTableAction
					color='primary'
					:icon='mdiInformationBox'
					:tooltip='isExpanded(internalItem) ? $t("components.iqrfnet.standard-manager.standard-devices.actions.hideInfo") : $t("components.iqrfnet.standard-manager.standard-devices.actions.showInfo")'
					@click='toggleExpand(internalItem)'
				/>
			</template>
			<template #expanded-row='{ columns, item }'>
				<td :colspan='columns.length'>
					<v-sheet border>
						<v-table
							class='mt-2'
							density='compact'
						>
							<caption class='text-left' style='padding-left: 16px;'>
								<strong>{{ $t('components.iqrfnet.standard-manager.standard-devices.info') }}</strong>
							</caption>
							<tbody>
								<tr>
									<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.headers.manufacturer') }}</th>
									<td>{{ item.getManufacturer() }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.headers.hwpid') }}</th>
									<td>{{ `${item.getHwpidHex()} [${item.getHwpid()}]` }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.headers.hwpidVer') }}</th>
									<td>{{ `${item.getHwpidVer() & 0x00_FF}.${item.getHwpidVer() & 0xFF_00}` }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.headers.mid') }}</th>
									<td>{{ `${item.getMidHex()} [${item.getMid()}]` }}</td>
								</tr>
							</tbody>
						</v-table>
						<v-divider v-if='item.hasSensor()' class='mb-2' />
						<v-table
							v-if='item.hasSensor()'
							density='compact'
						>
							<caption class='text-left' style='padding-left: 16px;'>
								<strong>{{ $t('components.iqrfnet.standard-manager.standard-devices.sensor.title') }}</strong>
							</caption>
							<thead>
								<tr>
									<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.sensor.type') }}</th>
									<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.sensor.name') }}</th>
									<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.sensor.index') }}</th>
									<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.sensor.updated') }}</th>
									<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.sensor.value') }}</th>
								</tr>
							</thead>
							<tbody>
								<tr
									v-for='(sensor, i) of item.getSensors()'
									:key='`sensor-${item.getAddress}-${i}`'
								>
									<td>{{ sensor.type }}</td>
									<td>{{ sensor.name }}</td>
									<td>{{ sensor.index }}</td>
									<td>{{ sensor.updated ?? $t('common.labels.notAvailable') }}</td>
									<td>{{ sensor.value ?? $t('common.labels.notAvailable') }}</td>
								</tr>
							</tbody>
						</v-table>
					</v-sheet>
				</td>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { DbMessages, IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { DbService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { NetworkService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { ProductService } from '@iqrf/iqrf-repository-client/services';
import { Product } from '@iqrf/iqrf-repository-client/types';
import {
	Action,
	ComponentState,
	IActionBtn,
	IBooleanIcon,
	ICard,
	IDataTable,
	IDataTableAction,
} from '@iqrf/iqrf-vue-ui';
import { mdiInformationBox } from '@mdi/js';
import { computed, onBeforeMount, onBeforeUnmount, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DatabaseResetDialog from '@/components/iqrfnet/standard-manager/DatabaseResetDialog.vue';
import EnumerationDialog from '@/components/iqrfnet/standard-manager/EnumerationDialog.vue';
import StandardDevice from '@/modules/standardDevice';
import { useRepositoryClient } from '@/services/RepositoryClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { DbDeviceData, DbSensors } from '@/types/DaemonApi/iqrfDb';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const daemonStore = useDaemonStore();
const i18n = useI18n();
const headers = computed(() => [
	{
		key: 'address',
		title: i18n.t('components.iqrfnet.standard-manager.standard-devices.headers.address'),
	},
	{
		key: 'product',
		title: i18n.t('components.iqrfnet.standard-manager.standard-devices.headers.product'),
	},
	{
		key: 'os',
		title: i18n.t('components.iqrfnet.standard-manager.standard-devices.headers.os'),
		sortable: false,
	},
	{
		key: 'dpa',
		title: i18n.t('components.iqrfnet.standard-manager.standard-devices.headers.dpa'),
	},
	{
		key: 'status',
		title: i18n.t('components.iqrfnet.standard-manager.standard-devices.headers.status'),
		sortable: false,
	},
	{
		key: 'sensor',
		title: i18n.t('components.iqrfnet.standard-manager.standard-devices.headers.sensor'),
		sortable: false,
	},
	{
		key: 'binout',
		title: i18n.t('components.iqrfnet.standard-manager.standard-devices.headers.binout'),
		sortable: false,
	},
	{
		key: 'light',
		title: i18n.t('components.iqrfnet.standard-manager.standard-devices.headers.light'),
		sortable: false,
	},
	{
		key: 'actions',
		title: i18n.t('common.columns.actions'),
		align: 'end',
		sortable: false,
	},
]);
const devices: Ref<StandardDevice[]> = ref([]);
const productService: Ref<ProductService | null> = ref(null);
const products = new Map<number, Product|null>();
let auxDevices: StandardDevice[] = [];
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			switch (rsp.mType) {
				case DbMessages.GetDevices:
					handleGetDevices(rsp);
					break;
				case DbMessages.GetLights:
					handleGetLights(rsp);
					break;
				case DbMessages.GetSensors:
					handleGetSensors(rsp);
					break;
				case IqmeshServiceMessages.Ping:
					handlePing(rsp);
					break;
			}
		});
	}
});

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.iqrfnet.standard-manager.standard-devices.noData.fetchError';
	}
	return 'components.iqrfnet.standard-manager.standard-devices.noData.empty';
});

async function getDevices(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	auxDevices = [];
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.devices.fetch.timeout'),
		() => {
			componentState.value = ComponentState.FetchFailed;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		DbService.getDevices(
			{ brief: false, addresses: [], sensors: false, binouts: true },
			opts,
		),
	);
}

function handleGetDevices(rsp: DaemonApiResponse): void {
	daemonStore.removeMessage(msgId.value);
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.FetchFailed;
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.devices.fetch.failed'),
		);
		return;
	}
	const items = rsp.data.rsp.devices as DbDeviceData[];
	if (items.length === 0) {
		componentState.value = ComponentState.Ready;
		devices.value = [];
		return;
	}
	for (const device of items) {
		auxDevices.push(new StandardDevice(device));
	}
	devices.value = auxDevices;
	getLights();
}

async function getLights(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.light.fetch.timeout'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		DbService.getLights(opts),
	);
}

function handleGetLights(rsp: DaemonApiResponse): void {
	daemonStore.removeMessage(msgId.value);
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.light.fetch.failed'),
		);
		return;
	}
	const lights = rsp.data.rsp.lightDevices as number[];
	for (const addr of lights) {
		const idx = getDeviceIndex(addr);
		if (idx !== -1) {
			devices.value[idx].setLight(true);
		}
	}
	getSensors();
}

async function getSensors(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.sensor.fetch.timeout'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		DbService.getSensors(opts),
	);
}

function handleGetSensors(rsp: DaemonApiResponse): void {
	daemonStore.removeMessage(msgId.value);
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.sensor.fetch.failed'),
		);
		return;
	}
	const sensors = rsp.data.rsp.sensorDevices as DbSensors[];
	for (const sensor of sensors) {
		const idx = getDeviceIndex(sensor.address);
		if (idx !== -1) {
			devices.value[idx].setSensors(sensor.sensors);
		}
	}
	fetchDeviceDetails();
	ping();
}

async function ping(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		90_000,
		i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.ping.failed'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		NetworkService.ping(
			{ repeat: 1, returnVerbose: true },
			{ hwpId: 0xFF_FF },
			opts,
		),
	);
}

function handlePing(rsp: DaemonApiResponse): void {
	componentState.value = ComponentState.Ready;
	daemonStore.removeMessage(msgId.value);
	if (rsp.data.status === 0) {
		const results = rsp.data.rsp.pingResult;
		for (const device of results) {
			const idx = getDeviceIndex(device.address);
			if (idx !== -1) {
				devices.value[idx].setOnline(device.result);
			}
		}
	} else if (rsp.data.status === 1_003) {
		// NO ONLINE NODES, that's okay
	} else {
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.ping.failed'),
		);
	}
}

async function fetchDeviceDetails(): Promise<void> {
	if (productService.value === null) {
		return;
	}
	for (const device of devices.value) {
		const hwpid = device.getHwpid();
		if (products.has(hwpid)) {
			const candidate = products.get(hwpid);
			if (candidate !== null) {
				device.setProduct(candidate);
			}
			continue;
		}
		if ((hwpid & 0xF) === 0xF) {
			continue;
		}
		try {
			const data = await productService.value.get(hwpid);
			products.set(hwpid, data);
			device.setProduct(data);
		} catch {
			products.set(hwpid, null);
		}
	}
}


function getDeviceIndex(address: number): number {
	return devices.value.findIndex((device: StandardDevice) => address === device.getAddress());
}

onBeforeMount(async () => {
	try {
		productService.value = (await useRepositoryClient()).getProductService();
	} catch {
		//
	}
});

onMounted(() => {
	getDevices();
});

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
