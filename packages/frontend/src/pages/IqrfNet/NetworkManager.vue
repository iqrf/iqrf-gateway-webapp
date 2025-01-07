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
		<h1>{{ $t('iqrfnet.networkManager.title') }}</h1>
		<v-row>
			<v-col lg='6'>
				<v-card>
					<v-tabs
						v-model='activeTab'
						:show-arrows='true'
					>
						<v-tab>
							{{ $t('iqrfnet.networkManager.iqmesh') }}
						</v-tab>
						<v-tab>
							{{ $t('iqrfnet.networkManager.autoNetwork.title') }}
						</v-tab>
						<v-tab>
							{{ $t('iqrfnet.networkManager.dpaParams.title') }}
						</v-tab>
						<v-tab>
							{{ $t('iqrfnet.networkManager.backupRestore.title') }}
						</v-tab>
						<v-tab>
							{{ $t('iqrfnet.networkManager.otaUpload.title') }}
						</v-tab>
						<v-tab>
							{{ $t('iqrfnet.networkManager.maintenance.title') }}
						</v-tab>
					</v-tabs>
					<v-tabs-items v-model='activeTab'>
						<v-tab-item :transition='false'>
							<BondingManager ref='bonding' @update-devices='updateDevices' />
							<v-divider />
							<DiscoveryManager @update-devices='updateDevices' />
						</v-tab-item>
						<v-tab-item :transition='false'>
							<AutoNetwork @update-devices='updateDevices' />
						</v-tab-item>
						<v-tab-item :transition='false'>
							<DpaParams />
						</v-tab-item>
						<v-tab-item :transition='false'>
							<Backup />
							<v-divider />
							<Restore @update-devices='updateDevices' />
						</v-tab-item>
						<v-tab-item :transition='false'>
							<OtaUpload />
						</v-tab-item>
						<v-tab-item :transition='false'>
							<Maintenance ref='maintenance' :rf-band='rfBand' />
						</v-tab-item>
					</v-tabs-items>
				</v-card>
			</v-col>
			<v-col lg='6'>
				<DevicesInfo ref='devices' />
			</v-col>
		</v-row>
	</div>
</template>

<script lang='ts'>
import {Component, Ref, Vue} from 'vue-property-decorator';
import AutoNetwork from '@/components/IqrfNet/NetworkManager/AutoNetwork/AutoNetwork.vue';
import Backup from '@/components/IqrfNet/NetworkManager/Backup/Backup.vue';
import BondingManager from '@/components/IqrfNet/NetworkManager/Iqmesh/BondingManager.vue';
import DevicesInfo from '@/components/IqrfNet/NetworkManager/Devices/DevicesInfo.vue';
import DiscoveryManager from '@/components/IqrfNet/NetworkManager/Iqmesh/DiscoveryManager.vue';
import DpaParams from '@/components/IqrfNet/NetworkManager/DpaParams/DpaParams.vue';
import Maintenance from '@/components/IqrfNet/NetworkManager/Maintenance/Maintenance.vue';
import OtaUpload from '@/components/IqrfNet/NetworkManager/OtaUpload/OtaUpload.vue';
import Restore from '@/components/IqrfNet/NetworkManager/Backup/Restore.vue';

import {compare} from 'compare-versions';

import IqrfNetService from '@/services/IqrfNetService';

import {MutationPayload} from 'vuex';
import {ToastOptions} from 'vue-toast-notification';

/**
 * Network manager page component
 */
@Component({
	components: {
		AutoNetwork,
		Backup,
		BondingManager,
		DevicesInfo,
		DiscoveryManager,
		DpaParams,
		Maintenance,
		OtaUpload,
		Restore,
	},
	metaInfo: {
		title: 'iqrfnet.networkManager.title',
	},
})
export default class NetworkManager extends Vue {
	/**
	 * @property {BondingManager} bondingManager Bonding manager component
	 */
	@Ref('bonding') bondingManager!: BondingManager;

	/**
	 * @property {DevicesInfo} devices Devices info component
	 */
	@Ref('devices') devices!: DevicesInfo;

	/**
	 * @property {Maintenance} maintenance Maintenance component
	 */
	@Ref('maintenance') maintenance!: Maintenance;

	/**
	 * @var {number} activeTab Default active tab
	 */
	private activeTab = 0;

	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {number} rfBand RF band
	 */
	private rfBand = 868;

	/**
	 * Mutation handler
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/*
	 * Watch handler
	 */
	private unwatch: CallableFunction = () => {return;};

	/**
	 * Registers mutation handling
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_EnumerateDevice') {
				this.handleEnumeration(mutation.payload.data);
			}
		});
	}

	/**
	 * Enumerates coordinator device for additional form configuration
	 */
	mounted(): void {
		if (this.$store.getters['daemonClient/isConnected']) {
			this.enumerateCoordinator();
		} else {
			this.unwatch = this.$store.watch(
				(_state, getter) => getter['daemonClient/isConnected'],
				(newVal, oldVal) => {
					if (!oldVal && newVal) {
						this.enumerateCoordinator();
						this.unwatch();
					}
				}
			);
		}
	}

	/**
	 * Unregisters mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
		this.unwatch();
	}

	/**
	 * Enumerates coordinator device
	 */
	private enumerateCoordinator(): void {
		IqrfNetService.enumerateDevice(0, 60000)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles enumeration Daemon API responses
	 * @param response Response
	 */
	private handleEnumeration(response): void {
		if (response.status !== 0) {
			return;
		}
		this.rfBand = Number.parseInt(response.rsp.trConfiguration.rfBand);
		const os = response.rsp.osRead.osBuild;
		if (parseInt(os, 16) < 0x08d7) {
			return;
		}
		const dpa = response.rsp.peripheralEnumeration.dpaVer;
		if (compare(dpa, '4.16', '<')) {
			return;
		}
		this.enableBondNfc();
	}

	/**
	 * Refreshes table of devices on update-devices event emitted by a bonding or discovery action
	 * @param {ToastOptions} message Success toast message passed from other components for devices info grid to show
	 */
	private updateDevices(message: ToastOptions): void {
		this.devices.getBondedDevices(message);
	}

	/**
	 * Enables NFC bonding in bonding manager
	 */
	private enableBondNfc(): void {
		this.bondingManager.enableBondNfc();
	}

}
</script>
