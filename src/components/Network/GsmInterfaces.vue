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
		<CCard>
			<CCardHeader class='datatable-header'>
				{{ $t("network.mobile.modems.title") }}
				<CButtonToolbar>
					<CButton
						v-if='monit !== null'
						:color='monit.enabled ? "danger" : "success"'
						class='float-right mr-1'
						size='sm'
						@click='toggleMonit(false)'
					>
						<CIcon :content='monit.enabled ? cilMediaStop : cilMediaPlay' size='sm' />
						<span v-if='monit.enabled'>
							{{ $t('network.mobile.table.stopMonit') }}
						</span>
						<span v-else>
							{{ $t('network.mobile.table.startMonit') }}
						</span>
					</CButton>
					<CButton
						v-if='hasBrokenGsmModem'
						color='warning'
						class='float-right mr-1'
						size='sm'
						@click='restartModemManager'
					>
						<CIcon :content='cilReload' size='sm' />
						{{ $t('network.mobile.table.restartModemManager') }}
					</CButton>
					<CButton
						color='secondary'
						size='sm'
						class='float-right mr-1'
						@click='scan'
					>
						<CIcon :content='cilSearch' size='sm' />
						{{ $t('forms.scan') }}
					</CButton>
					<CButton
						color='primary'
						size='sm'
						class='float-right'
						@click='getData(true)'
					>
						<CIcon :content='cilReload' size='sm' />
						{{ $t('forms.refresh') }}
					</CButton>
				</CButtonToolbar>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
					:items='modems'
					:items-per-page='20'
					:pagination='true'
					:loading='loading'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('network.mobile.messages.noInterfaces') }}
					</template>
					<template #rssi='{item}'>
						<td>
							{{ item.rssi !== null ? item.rssi + ' dBm' : '-' }}
						</td>
					</template>
					<template #signal='{item}'>
						<td>
							<SignalIndicator :signal='item.signal' />
						</td>
					</template>
					<template #state='{item}'>
						<td>
							<CBadge :color='stateColor(item.state)'>
								{{ $t(`network.mobile.modems.states.${item.state}`) }}
								<span v-if='item.state === "failed"'>
									({{ $t(`network.mobile.modems.failedReasons.${item.failedReason}`) }})
								</span>
							</CBadge>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			:show='showMonitWithoutConnectionModal'
			color='danger'
			size='lg'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.mobile.modals.enableMonitWithoutConnection.title') }}
				</h5>
			</template>
			{{ $t('network.mobile.modals.enableMonitWithoutConnection.body') }}
			<template #footer>
				<CButton
					color='secondary'
					class='ml-auto'
					@click='showMonitWithoutConnectionModal = false'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
		<CModal
			:show='showMonitWithoutActiveConnectionModal'
			color='warning'
			size='lg'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.mobile.modals.enableMonitWithoutActiveConnection.title') }}
				</h5>
			</template>
			{{ $t('network.mobile.modals.enableMonitWithoutActiveConnection.body') }}
			<template #footer>
				<CButton
					color='warning'
					@click='toggleMonit(true)'
				>
					{{ $t('table.actions.enable') }}
				</CButton>
				<CButton
					color='secondary'
					class='ml-auto'
					@click='showMonitWithoutActiveConnectionModal = false'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {cilMediaPlay, cilMediaStop, cilReload, cilSearch} from '@coreui/icons';
import {
	CBadge,
	CButton,
	CButtonToolbar,
	CCard,
	CCardBody,
	CCardHeader,
	CDataTable,
	CIcon,
	CModal,
} from '@coreui/vue/src';
import {AxiosResponse} from 'axios';
import {Component, PropSync, Vue, Watch} from 'vue-property-decorator';

import SignalIndicator from '@/components/Network/SignalIndicator.vue';
import {ModemState} from '@/enums/Network/ModemState';
import {IField} from '@/interfaces/Coreui';
import {IModem} from '@/interfaces/Network/Mobile';
import {MonitCheck} from '@/interfaces/Maintenance/Monit';
import MonitService from '@/services/MonitService';
import NetworkInterfaceService from '@/services/NetworkInterfaceService';
import ServiceService from '@/services/ServiceService';
import {NetworkConnection} from '@/interfaces/Network/Connection';

/**
 * GSM modem interface list
 */
@Component({
	components: {
		CBadge,
		CButton,
		CButtonToolbar,
		CCard,
		CCardHeader,
		CCardBody,
		CDataTable,
		CIcon,
		CModal,
		SignalIndicator,
	},
	data: () => ({
		cilMediaPlay,
		cilMediaStop,
		cilReload,
		cilSearch,
	}),
})
export default class GsmInterfaces extends Vue {

	/**
	 * @property {Array<IConnection>} _connections Array of connections
	 */
	@PropSync('connections', {type: Array, required: true}) _connections!: Array<NetworkConnection>;

	/**
	 * @property {Array<IField>} fields Array of CoreUI data table fields
	 */
	private fields: Array<IField> = [
		{
			key: 'interface',
			label: this.$t('network.mobile.modems.interface').toString(),
		},
		{
			key: 'imei',
			label: this.$t('network.mobile.modems.imei').toString(),
		},
		{
			key: 'manufacturer',
			label: this.$t('network.mobile.modems.manufacturer').toString(),
		},
		{
			key: 'model',
			label: this.$t('network.mobile.modems.model').toString(),
		},
		{
			key: 'state',
			label: this.$t('network.mobile.modems.state').toString(),
		},
		{
			key: 'signal',
			label: this.$t('network.mobile.modems.signal').toString(),
		},
		{
			key: 'rssi',
			label: this.$t('network.mobile.modems.rssi').toString(),
		},
	];

	/**
	 * @property {Array<IModem>} modems Array of modems
	 */
	private modems: Array<IModem> = [];

	/**
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

	/**
	 * @property {MonitCheck | null} monit Monit check
	 */
	private monit: MonitCheck | null = null;

	/**
	 * @property {string} monitCheckName Monit check name
	 */
	private monitCheckName = 'network_ppp0';

	/**
	 * @property {boolean} showMonitWithoutConnectionModal Show Monit without connection modal
	 */
	private showMonitWithoutConnectionModal = false;

	/**
	 * @property {boolean} showMonitWithoutActiveConnectionModal Show Monit without active connection modal
	 */
	private showMonitWithoutActiveConnectionModal = false;

	/**
	 * @property {boolean} hasBrokenGsmModem Checks if the used modem is broken to prevent hanging on
	 */
	get hasBrokenGsmModem(): boolean {
		return this.$store.getters['gateway/board'] === 'MICRORISC s.r.o. IQD-GW04';
	}

	/**
	 * @property {boolean} hasConnection Checks if there is no connection
	 */
	get hasConnections(): boolean {
		return this._connections.length !== 0;
	}

	/**
	 * @property {boolean} hasActiveConnection Checks if there is an active connection
	 */
	get hasActiveConnection(): boolean {
		return this._connections.some((connection: NetworkConnection) => connection.interfaceName !== null);
	}

	/**
	 * Retrieves modems at page load
	 */
	protected mounted(): void {
		this.getData();
	}

	/**
	 * Retrieves modems
	 */
	public async getData(buttonInvoked = false): Promise<void> {
		this.loading = true;
		await NetworkInterfaceService.listModems()
			.then((modems: Array<IModem>) => {
				this.modems = modems;
				this.loading = false;
				if (buttonInvoked) {
					this.$emit('refresh');
				}
			});
		await this.getMonitCheck();
	}

	@Watch('gateway.info')
	public async getMonitCheck(): Promise<void> {
		if (!this.$store.getters['features/isEnabled']('monit') || !this.hasBrokenGsmModem) {
			return;
		}
		await MonitService.getCheck(this.monitCheckName)
			.then((response: AxiosResponse<MonitCheck>) => {
				this.monit = response.data;
			})
			.catch(() => {
				this.monit = null;
			});
	}

	/**
	 * Scan modems
	 */
	public scan(): void {
		this.loading = true;
		NetworkInterfaceService.scanModems()
			.then(async () => {
				await new Promise((resolve) => setTimeout(resolve, 5_000));
				await this.getData();
			})
			.catch(() => {
				this.loading = false;
			});
	}

	/**
	 * Returns badge color for modem state
	 * @param {ModemState} state Modem state
	 */
	private stateColor(state: ModemState): string {
		switch (state) {
			case ModemState.failed:
				return 'danger';
			case ModemState.locked:
			case ModemState.unknown:
				return 'warning';
			case ModemState.connected:
				return 'success';
			case ModemState.registered:
				return 'info';
			default:
				return 'secondary';
		}
	}

	/**
	 * Restarts ModemManager service to fix broken modem
	 */
	private async restartModemManager(): Promise<void> {
		this.$emit('restart');
		this.loading = true;
		ServiceService.restart('ModemManager')
			.then(async () => {
				await new Promise(resolve => setTimeout(resolve, 15_000));
				await this.getData();
				this.loading = false;
				this.$emit('refresh');
			})
			.catch(() => {
				this.loading = false;
				this.$emit('refresh');
			});
	}

	/**
	 * Toggles monit check
	 * @param {boolean} confirmed Confirmed
	 */
	private toggleMonit(confirmed: boolean = false): void {
		if (this.monit === null) {
			return;
		}
		const enabled = this.monit.enabled;
		console.warn(
			{
				enabled,
				hasConnections: this.hasConnections,
				hasActiveConnection: this.hasActiveConnection,
			}
		);
		if (!enabled) {
			if (!this.hasConnections) {
				this.showMonitWithoutConnectionModal = true;
				return;
			}
			if (!this.hasActiveConnection) {
				if (confirmed) {
					this.showMonitWithoutActiveConnectionModal = false;
				} else {
					this.showMonitWithoutActiveConnectionModal = true;
					return;
				}
			}
		}

		this.loading = true;
		(enabled
			? MonitService.disableCheck(this.monitCheckName)
			: MonitService.enableCheck(this.monitCheckName))
			.then(async () => {
				await ServiceService.restart('monit');
				await this.getData();
				this.$toast.success(
					this.$t(`network.mobile.messages.monit${enabled ? 'Disabled' : 'Enabled'}Successfully`).toString()
				);
				this.loading = false;
			})
			.catch(() => {
				this.$toast.error(
					this.$t(`network.mobile.messages.monit${enabled ? 'Disable' : 'Enable'}Failed`).toString()
				);
				this.loading = false;
			});
	}

}
</script>
