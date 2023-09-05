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
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='modems'
					:no-data-text='$t("network.mobile.messages.noInterfaces")'
				>
					<template #top>
						<v-toolbar dense flat>
							<h5>{{ $t("network.mobile.modems.title") }}</h5>
							<v-spacer />
							<v-btn
								v-if='monit !== null'
								:color='monit.enabled ? "error" : "success"'
								class='mr-1'
								small
								@click='toggleMonit'
							>
								<v-icon :icon='monit.enabled ? "mdi-stop" : "mdi-play"' small />
								<span v-if='monit.enabled'>
									{{ $t('network.mobile.table.stopMonit') }}
								</span>
								<span v-else>
									{{ $t('network.mobile.table.startMonit') }}
								</span>
							</v-btn>
							<v-btn
								v-if='hasBrokenGsmModem'
								color='warning'
								class='mr-1'
								small
								@click='restartModemManager'
							>
								<v-icon small>
									mdi-refresh
								</v-icon>
								{{ $t('network.mobile.table.restartModemManager') }}
							</v-btn>
							<v-btn
								color='secondary'
								small
								class='mr-1'
								@click='scan'
							>
								<v-icon small>
									mdi-magnify
								</v-icon>
							</v-btn>
							<v-btn
								color='primary'
								small
								@click='getData(true)'
							>
								<v-icon small>
									mdi-refresh
								</v-icon>
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.rssi`]='{item}'>
						{{ item.rssi !== null ? item.rssi + ' dBm' : '-' }}
					</template>
					<template #[`item.signal`]='{item}'>
						<SignalIndicator :signal='item.signal' />
					</template>
					<template #[`item.state`]='{item}'>
						<v-chip
							:color='stateColor(item.state)'
							label
						>
							{{ $t(`network.mobile.modems.states.${item.state}`) }}
							<span v-if='item.state === ModemState.failed'>
								({{ $t(`network.mobile.modems.failedReasons.${item.failedReason}`) }})
							</span>
						</v-chip>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
		<v-dialog
			v-model='showMonitWithoutConnectionModal'
			width='50%'
			persistent
			no-click-animation
		>
			<v-card>
				<v-card-title>
					{{ $t('network.mobile.modals.enableMonitWithoutConnection.title') }}
				</v-card-title>
				<v-card-text>
					{{ $t('network.mobile.modals.enableMonitWithoutConnection.body') }}
				</v-card-text>
				<v-card-actions>
					<v-btn
						color='secondary'
						class='ml-auto'
						@click='showMonitWithoutConnectionModal = false'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-dialog
			v-model='showMonitWithoutConnectionModal'
			width='50%'
			persistent
			no-click-animation
		>
			<v-card>
				<v-card-title>
					{{ $t('network.mobile.modals.enableMonitWithoutActiveConnection.title') }}
				</v-card-title>
				<v-card-text>
					{{ $t('network.mobile.modals.enableMonitWithoutActiveConnection.body') }}
				</v-card-text>
				<v-card-actions>
					<v-btn
						color='warning'
						@click='toggleMonit(true)'
					>
						{{ $t('table.actions.enable') }}
					</v-btn>
					<v-btn
						color='secondary'
						class='ml-auto'
						@click='showMonitWithoutActiveConnectionModal = false'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>

<script lang='ts'>
import {Component, PropSync, Vue, Watch} from 'vue-property-decorator';
import SignalIndicator from '@/components/Network/SignalIndicator.vue';

import {ModemState} from '@/enums/Network/ModemState';

import {useApiClient} from '@/services/ApiClient';
import MonitService from '@/services/MonitService';
import NetworkInterfaceService from '@/services/NetworkInterfaceService';

import {AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IModem} from '@/interfaces/Network/Mobile';
import {MonitCheck} from '@/interfaces/Maintenance/Monit';
import {NetworkConnection} from '@/interfaces/Network/Connection';

/**
 * GSM modem interface list
 */
@Component({
	components: {
		SignalIndicator,
	},
	data: () => ({
		ModemState,
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
	private loading = true;

	/**
	 * @property {boolean} hasBrokenGsmModem Checks if the used modem is broken to prevent hanging on
	 */
	get hasBrokenGsmModem(): boolean {
		return this.$store.getters['gateway/board'] === 'MICRORISC s.r.o. IQD-GW04';
	}

	/**
	 * @property {Array<IModem>} modems Array of modems
	 */
	private modems: Array<IModem> = [];

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
	 * @property {Array<DataTableHeader>} fields Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'interface',
			text: this.$t('network.mobile.modems.interface').toString(),
		},
		{
			value: 'imei',
			text: this.$t('network.mobile.modems.imei').toString(),
		},
		{
			value: 'manufacturer',
			text: this.$t('network.mobile.modems.manufacturer').toString(),
		},
		{
			value: 'model',
			text: this.$t('network.mobile.modems.model').toString(),
		},
		{
			value: 'state',
			text: this.$t('network.mobile.modems.state').toString(),
		},
		{
			value: 'signal',
			text: this.$t('network.mobile.modems.signal').toString(),
		},
		{
			value: 'rssi',
			text: this.$t('network.mobile.modems.rssi').toString(),
		},
	];

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
		NetworkInterfaceService.listModems()
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
				return 'error';
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
		await useApiClient().getServiceService().restart('ModemManager')
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
				await useApiClient().getServiceService().restart('monit');
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
