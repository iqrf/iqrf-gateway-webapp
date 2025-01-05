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
	<CCard>
		<CCardHeader class='datatable-header'>
			{{ $t("network.interface.title") }}
			<CButtonToolbar>
				<CButton
					color='primary'
					size='sm'
					class='float-right'
					@click='getData'
				>
					<CIcon :content='cilReload' size='sm' />
					{{ $t('forms.refresh') }}
				</CButton>
			</CButtonToolbar>
		</CCardHeader>
		<CCardBody>
			<CDataTable
				:fields='fields'
				:items='interfaces'
				:items-per-page='20'
				:pagination='true'
				:loading='loading'
				:sorter='{external: false, resetable: true}'
			>
				<template #no-items-view='{}'>
					{{ $t('network.interface.messages.noInterfaces') }}
				</template>
				<template #manufacturer='{item}'>
					<td>{{ item.manufacturer }}</td>
				</template>
				<template #model='{item}'>
					<td>{{ item.model }}</td>
				</template>
				<template #macAddress='{item}'>
					<td>{{ item.macAddress }}</td>
				</template>
				<template #state='{item}'>
					<td>
						<CBadge :color='stateColor(item.state)'>
							{{ $t(`network.interface.states.${item.state}`) }}
						</CBadge>
					</td>
				</template>
			</CDataTable>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {cilReload} from '@coreui/icons';
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
import {Component, Prop, Vue} from 'vue-property-decorator';

import {IField} from '@/interfaces/Coreui';
import {NetworkInterface} from '@/interfaces/Network/Connection';
import {InterfaceState} from '@/enums/Network/InterfaceState';
import {InterfaceType} from '@/enums/Network/InterfaceType';
import NetworkInterfaceService from '@/services/NetworkInterfaceService';

/**
 * Network interface list
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
	},
	data: () => ({
		cilReload,
	}),
})
export default class NetworkInterfaces extends Vue {

	/**
	 * @property {NetworkInterface} type Network interface type
	 */
	@Prop({required: true}) type!: InterfaceType;

	/**
	 * @property {Array<IField>} fields Array of CoreUI data table fields
	 */
	get fields(): Array<IField> {return [
		{
			key: 'name',
			label: this.$t('network.interface.name').toString(),
		},
		{
			key: 'manufacturer',
			label: this.$t('network.interface.manufacturer').toString(),
		},
		{
			key: 'model',
			label: this.$t('network.interface.model').toString(),
		},
		{
			key: 'macAddress',
			label: this.$t('network.interface.macAddress').toString(),
		},
		{
			key: 'state',
			label: this.$t('network.interface.state').toString(),
		},
	];
	}

	/**
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

	/**
	 * @property {Array<NetworkInterface>} interfaces Array of networkInterfaces
   */
	private interfaces: Array<NetworkInterface> = [];

	/**
	 * Retrieves network interfaces at component creation
	 */
	protected mounted(): void {
		this.getData();
	}

	/**
	 * Retrieves network interfaces
	 */
	public getData(): void {
		this.loading = true;
		NetworkInterfaceService.list(this.type)
			.then((interfaces: Array<NetworkInterface>) => {
				this.interfaces = interfaces;
				this.loading = false;
			});
	}

	/**
	 * Returns badge color based on interface state
	 * @param {InterfaceState} state Interface state
	 */
	private stateColor(state: InterfaceState): string {
		const match = state.match(/^(?<state>\w+)( (.*))?$/);
		switch (match?.groups?.state) {
			case 'connected':
				return 'success';
			case 'connecting':
				return 'primary';
			case 'deactivating':
				return 'warning';
			case 'disconnected':
				return 'danger';
			default:
				return 'secondary';
		}
	}

}
</script>
