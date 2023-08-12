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
							color='warning'
							class='mr-1'
							small
							@click='hasBrokenGsmModem'
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
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {DataTableHeader} from 'vuetify';

import SignalIndicator from '@/components/Network/SignalIndicator.vue';
import {ModemState} from '@/enums/Network/ModemState';
import {IModem} from '@/interfaces/Network/Mobile';
import NetworkInterfaceService from '@/services/NetworkInterfaceService';
import {useApiClient} from '@/services/ApiClient';
import {MonitCheck} from '@/interfaces/Maintenance/Monit';
import MonitService from '@/services/MonitService';
import { AxiosResponse } from 'axios';

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
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

	/**
	 * @property {boolean} hasBrokenGsmModem Checks if the used modem is broken to prevent hanging on
	 */
	get hasBrokenGsmModem(): boolean {
		return this.$store.getters['gateway/board'] === 'MICRORISC s.r.o. IQD-GW04' &&
				this.modems.filter((modem: IModem): boolean => modem.interface === 'ttyAMA2').length !== 0;
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
		if (this.$store.getters['features/isEnabled']('monit') && this.hasBrokenGsmModem) {
			await MonitService.getCheck(this.monitCheckName)
				.then((response: AxiosResponse<MonitCheck>) => {
					this.monit = response.data;
				})
				.catch(() => {
					this.monit = null;
				});
		}
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
	 */
	private toggleMonit(): void {
		if (this.monit === null) {
			return;
		}
		this.loading = true;
		const enabled = this.monit.enabled;
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
