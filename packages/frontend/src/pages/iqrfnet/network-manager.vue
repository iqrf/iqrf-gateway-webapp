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
	<div>
		<Head>
			<title>{{ $t('pages.iqrfnet.network-manager.title') }}</title>
		</Head>
		<v-row>
			<v-col lg='6' cols='12'>
				<v-toolbar
					density='compact'
					class='rounded-t'
					color='primary'
				>
					<template #title>
						<v-tabs v-model='tab'>
							<v-tab
								value='0'
								:text='$t("components.iqrfnet.network-manager.iqmesh")'
							/>
							<v-tab
								value='1'
								:text='$t("components.iqrfnet.network-manager.dpa-params.title")'
							/>
							<v-tab
								value='2'
								:text='$t("components.iqrfnet.network-manager.backup-restore")'
							/>
							<v-tab
								value='3'
								:text='$t("components.iqrfnet.network-manager.ota-upload.title")'
							/>
							<v-tab
								value='4'
								:text='$t("components.iqrfnet.network-manager.maintenance")'
							/>
						</v-tabs>
					</template>
				</v-toolbar>
				<v-tabs-window v-model='tab'>
					<v-tabs-window-item
						value='0'
					>
						<BondingManager @update-devices='refreshDevices()' />
						<NfcBondingManager v-if='showNfc' />
						<DiscoveryManager @update-devices='refreshDevices()' />
					</v-tabs-window-item>
					<v-tabs-window-item
						value='1'
					>
						<DpaValue />
						<DpaHops />
						<FrcParams />
					</v-tabs-window-item>
					<v-tabs-window-item
						value='2'
					>
						<BackupManager />
						<RestoreManager @update-devices='refreshDevices()' />
					</v-tabs-window-item>
					<v-tabs-window-item
						value='3'
					>
						<OtaUpload />
					</v-tabs-window-item>
					<v-tabs-window-item
						value='4'
					>
						<FrcResponseTime />
						<RfSignalTest ref='rfSignalComponent' />
						<NetworkIssuesResolver />
					</v-tabs-window-item>
				</v-tabs-window>
			</v-col>
			<v-col lg='6' cols='12'>
				<Devices ref='devicesComponent' />
			</v-col>
		</v-row>
	</div>
</template>

<route>
{
	"name": "NetworkManager",
}
</route>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { EnumerationService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Head } from '@unhead/vue/components';
import { compare } from 'compare-versions';
import { storeToRefs } from 'pinia';
import { onBeforeUnmount, onMounted, ref, Ref, watch } from 'vue';

import BackupManager from '@/components/iqrfnet/network-manager/BackupManager.vue';
import BondingManager from '@/components/iqrfnet/network-manager/BondingManager.vue';
import Devices from '@/components/iqrfnet/network-manager/Devices.vue';
import DiscoveryManager from '@/components/iqrfnet/network-manager/DiscoveryManager.vue';
import DpaHops from '@/components/iqrfnet/network-manager/DpaHops.vue';
import DpaValue from '@/components/iqrfnet/network-manager/DpaValue.vue';
import FrcParams from '@/components/iqrfnet/network-manager/FrcParams.vue';
import FrcResponseTime from '@/components/iqrfnet/network-manager/FrcResponseTime.vue';
import NetworkIssuesResolver from '@/components/iqrfnet/network-manager/NetworkIssuesResolver.vue';
import NfcBondingManager from '@/components/iqrfnet/network-manager/NfcBondingManager.vue';
import OtaUpload from '@/components/iqrfnet/network-manager/OtaUpload.vue';
import RestoreManager from '@/components/iqrfnet/network-manager/RestoreManager.vue';
import RfSignalTest from '@/components/iqrfnet/network-manager/RfSignalTest.vue';
import { useDaemonStore } from '@/store/daemonSocket';

const daemonStore = useDaemonStore();
const { isConnected } = storeToRefs(daemonStore);
const msgId: Ref<string | null> = ref(null);
const tab: Ref<number> = ref(0);
const devicesComponent: Ref<typeof Devices | null> = ref(null);
const rfSignalComponent: Ref<typeof RfSignalTest | null> = ref(null);
const showNfc: Ref<boolean> = ref(false);

function refreshDevices(): void {
	if (devicesComponent.value !== null) {
		devicesComponent.value.getBondedDevices();
	}
}

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			if (rsp.mType === IqmeshServiceMessages.Enumerate) {
				handleEnumerate(rsp);
			}
		});
	}
});

async function enumerate(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		60_000,
		null,
		() => {
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		EnumerationService.enumerate(
			{ repeat: 1, returnVerbose: true },
			{ deviceAddr: 0 },
			opts,
		),
	);
}

function handleEnumerate(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		return;
	}
	const rfBand = Number.parseInt(rsp.data.rsp.trConfiguration.rfBand);
	rfSignalComponent.value?.setRfChannel(rfBand);
	const os = rsp.data.rsp.osRead.osBuild;
	if (Number.parseInt(os, 16) < 0x08_D7) {
		return;
	}
	const dpa = rsp.data.rsp.peripheralEnumeration.dpaVer;
	if (compare(dpa, '4.16', '<')) {
		return;
	}
	showNfc.value = true;

}

onMounted(() => {
	if (isConnected.value) {
		enumerate();
	} else {
		watch(isConnected, (newVal: boolean, oldVal: boolean): void => {
			if (newVal && !oldVal) {
				enumerate();
			}
		}, { once: true });
	}
});

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
