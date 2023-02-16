<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<CRow>
			<CCol lg='6'>
				<CCard>
					<CTabs variant='tabs' :active-tab='activeTab'>
						<CTab :title='$t("iqrfnet.networkManager.iqmesh")'>
							<BondingManager ref='bonding' @update-devices='updateDevices' />
							<DiscoveryManager @update-devices='updateDevices' />
						</CTab>
						<CTab :title='$t("iqrfnet.networkManager.autoNetwork.title")'>
							<AutoNetwork @update-devices='updateDevices' />
						</CTab>
						<CTab :title='$t("iqrfnet.networkManager.dpaParams.title")'>
							<DpaParams />
						</CTab>
						<CTab :title='$t("iqrfnet.networkManager.backupRestore.title")'>
							<Backup />
							<Restore />
						</CTab>
						<CTab :title='$t("iqrfnet.networkManager.otaUpload.title")'>
							<OtaUpload />
						</CTab>
						<CTab :title='$t("iqrfnet.networkManager.maintenance.title")'>
							<Maintenance ref='maintenance' />
						</CTab>
					</CTabs>
				</CCard>
			</CCol>
			<CCol lg='6'>
				<DevicesInfo ref='devices' />
			</CCol>
		</CRow>
	</div>
</template>

<script lang='ts'>
import {Component, Ref, Vue} from 'vue-property-decorator';
import {CCard, CTab, CTabs} from '@coreui/vue/src';

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
		CCard,
		CTab,
		CTabs,
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
		const rfBand = Number.parseInt(response.rsp.trConfiguration.rfBand);
		this.setRfChannelRules(rfBand);
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
	 * Passes RF band to the RF Signal Test component
	 * @param {number} rfBand RF Band
	 */
	private setRfChannelRules(rfBand: number): void {
		this.maintenance.setRfChannelRules(rfBand);
	}

	/**
	 * Enables NFC bonding in bonding manager
	 */
	private enableBondNfc(): void {
		this.bondingManager.enableBondNfc();
	}
}
</script>
