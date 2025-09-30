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
						</v-tabs>
					</template>
				</v-toolbar>
				<v-tabs-window v-model='tab'>
					<v-tabs-window-item
						value='0'
					>
						<BondingManager @update-devices='refreshDevices()' />
						<NfcBondingManager />
						<DiscoveryManager @update-devices='refreshDevices()' />
					</v-tabs-window-item>
					<v-tabs-window-item
						value='1'
					>
						<DpaValue />
						<DpaHops />
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
import { Head } from '@unhead/vue/components';
import { ref, Ref } from 'vue';

import BondingManager from '@/components/iqrfnet/network-manager/BondingManager.vue';
import Devices from '@/components/iqrfnet/network-manager/Devices.vue';
import DiscoveryManager from '@/components/iqrfnet/network-manager/DiscoveryManager.vue';
import DpaHops from '@/components/iqrfnet/network-manager/DpaHops.vue';
import DpaValue from '@/components/iqrfnet/network-manager/DpaValue..vue';
import NfcBondingManager from '@/components/iqrfnet/network-manager/NfcBondingManager.vue';

const tab: Ref<number> = ref(0);
const devicesComponent: Ref<typeof Devices | null> = ref(null);

function refreshDevices(): void {
	if (devicesComponent.value !== null) {
		devicesComponent.value.getBondedDevices();
	}
}

</script>
