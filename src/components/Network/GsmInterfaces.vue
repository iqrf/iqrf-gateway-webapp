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
	<CCard>
		<CCardHeader class='datatable-header'>
			{{ $t("network.mobile.modems.title") }}
			<CButtonToolbar>
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
</template>

<script lang='ts'>
import {cilReload, cilSearch} from '@coreui/icons';
import {
	CBadge,
	CButton,
	CButtonToolbar,
	CCard,
	CCardBody,
	CCardHeader,
	CDataTable,
	CIcon,
} from '@coreui/vue/src';
import {Component, Vue} from 'vue-property-decorator';

import SignalIndicator from '@/components/Network/SignalIndicator.vue';
import {ModemState} from '@/enums/Network/ModemState';
import {IField} from '@/interfaces/Coreui';
import {IModem} from '@/interfaces/Network/Mobile';
import NetworkInterfaceService from '@/services/NetworkInterfaceService';
import ServiceService from '@/services/ServiceService';

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
		SignalIndicator,
	},
	data: () => ({
		cilReload,
		cilSearch,
	}),
})
export default class GsmInterfaces extends Vue {

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
	 * @property {boolean} hasBrokenGsmModem Checks if the used modem is broken to prevent hanging on
	 */
	get hasBrokenGsmModem(): boolean {
		return this.$store.getters['gateway/board'] === 'MICRORISC s.r.o. IQD-GW04' &&
				this.modems.filter((modem: IModem): boolean => modem.interface === 'ttyAMA2').length !== 0;
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
	public getData(buttonInvoked = false): void {
		this.loading = true;
		NetworkInterfaceService.listModems()
			.then((modems: Array<IModem>) => {
				this.modems = modems;
				this.loading = false;
				if (buttonInvoked) {
					this.$emit('refresh');
				}
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
				this.getData();
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
				this.getData();
				this.loading = false;
				this.$emit('refresh');
			})
			.catch(() => {
				this.loading = false;
				this.$emit('refresh');
			});
	}
}
</script>
