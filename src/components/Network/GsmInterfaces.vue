<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<CCardHeader>
			{{ $t("network.mobile.modems.title") }}
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
						{{ $t(`network.mobile.modems.states.${item.state}`) }}
						<span v-if='item.state === "failed"'>
							({{ $t(`network.mobile.modems.failedReasons.${item.failedReason}`) }})
						</span>
					</td>
				</template>
			</CDataTable>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {IField} from '@/interfaces/Coreui';

import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CDataTable} from '@coreui/vue/src';
import {IModem} from '@/interfaces/Network/Mobile';
import NetworkInterfaceService from '@/services/NetworkInterfaceService';
import SignalIndicator from '@/components/Network/SignalIndicator.vue';

/**
 * GSM modem interface list
 */
@Component({
	components: {
		CCard,
		CCardHeader,
		CCardBody,
		CDataTable,
		SignalIndicator,
	},
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

	protected mounted(): void {
		NetworkInterfaceService.listModems()
			.then((modems: Array<IModem>) => {
				this.modems = modems;
				this.loading = false;
			});
	}

}
</script>
