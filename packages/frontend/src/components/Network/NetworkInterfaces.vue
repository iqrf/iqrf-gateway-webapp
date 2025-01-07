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
	<v-card>
		<v-card-text>
			<v-data-table
				:loading='loading'
				:headers='headers'
				:items='interfaces'
				:no-data-text='$t("network.interface.messages.noInterfaces")'
			>
				<template #top>
					<v-toolbar dense flat>
						<h5>{{ $t('network.interface.title') }}</h5>
						<v-spacer />
						<v-btn
							color='primary'
							small
							@click='getInterfaces'
						>
							<v-icon small>
								mdi-refresh
							</v-icon>
						</v-btn>
					</v-toolbar>
				</template>
				<template #[`item.manufacturer`]='{item}'>
					{{ item.manufacturer }}
				</template>
				<template #[`item.model`]='{item}'>
					{{ item.model }}
				</template>
				<template #[`item.macAddress`]='{item}'>
					{{ item.macAddress }}
				</template>
				<template #[`item.state`]='{item}'>
					<v-chip
						:color='stateColor(item.state)'
						label
						small
					>
						{{ $t(`network.interface.states.${item.state}`) }}
					</v-chip>
				</template>
			</v-data-table>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {DataTableHeader} from 'vuetify';
import {
	NetworkInterface,
	NetworkInterfaceState, NetworkInterfaceType
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {useApiClient} from '@/services/ApiClient';

/**
 * Network interface list
 */
@Component
export default class NetworkInterfaces extends Vue {

	/**
	 * @property {NetworkInterface} type Network interface type
	 */
	@Prop({required: true}) type!: NetworkInterfaceType;

	/**
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

	/**
	 * @property {Array<NetworkInterface>} interfaces Array of networkInterfaces
   */
	private interfaces: Array<NetworkInterface> = [];

	/**
	 * @property {Array<IField>} headers Data table headers
	 */
	get headers(): Array<DataTableHeader> {return [
		{
			value: 'name',
			text: this.$t('network.interface.name').toString(),
		},
		{
			value: 'manufacturer',
			text: this.$t('network.interface.manufacturer').toString(),
		},
		{
			value: 'model',
			text: this.$t('network.interface.model').toString(),
		},
		{
			value: 'macAddress',
			text: this.$t('network.interface.macAddress').toString(),
		},
		{
			value: 'state',
			text: this.$t('network.interface.state').toString(),
		},
	];
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	protected mounted(): void {
		this.getInterfaces();
	}

	/**
	 * Retrieves interfaces
	 */
	public getInterfaces(): void {
		this.loading = true;
		useApiClient().getNetworkServices().getNetworkInterfaceService().list(this.type)
			.then((interfaces: Array<NetworkInterface>) => {
				this.interfaces = interfaces;
				this.loading = false;
			});
	}

	/**
	 * Returns badge color based on interface state
	 * @param {NetworkInterfaceState} state Interface state
	 */
	private stateColor(state: NetworkInterfaceState): string {
		const match = state.match(/^(?<state>\w+)( (.*))?$/);
		switch (match?.groups?.state) {
			case 'connected':
				return 'success';
			case 'connecting':
				return 'primary';
			case 'deactivating':
				return 'warning';
			case 'disconnected':
				return 'error';
			default:
				return 'secondary';
		}
	}

}
</script>
