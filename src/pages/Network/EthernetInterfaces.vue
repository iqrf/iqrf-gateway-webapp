<template>
	<div>
		<h1>{{ $t('network.ethernet.title') }}</h1>
		<CCard v-for='iface of interfaces' :key='iface.name'>
			<CCardHeader class='d-flex'>
				{{ iface.name }}
				<CBadge :color='getStatusBadgeColor(iface.state)' class='ml-auto'>
					{{ iface.state }}
				</CBadge>
			</CCardHeader>
			<CCardBody>
				<EthernetConnections
					:connections='connections'
					:interface-name='iface.name'
					@updateConnection='getConnections'
				/>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CCard, CCardBody, CCardHeader} from '@coreui/vue/src';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceState, InterfaceType} from '../../services/NetworkInterfaceService';
import EthernetConnections from '../../components/Network/EthernetConnections.vue';
import { AxiosResponse } from 'axios';
import { NetworkConnection, NetworkInterface } from '../../interfaces/network';

@Component({
	components: {
		EthernetConnections,
		CBadge,
		CCard,
		CCardBody,
		CCardHeader,
	},
	metaInfo: {
		title: 'network.ethernet.title',
	},
})

/**
 * Ethernet interfaces page component
 */
export default class EthernetInterfaces extends Vue {
	/**
	 * @var {Array<NetworkConnection>} connections Array of existing network connections
	 */
	private connections: Array<NetworkConnection> = []

	/**
	 * @var {Array<NetworkInterfaces} interfaces Array of existing network interfaces
	 */
	private interfaces: Array<NetworkInterface> = []

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		NetworkInterfaceService.list(InterfaceType.ETHERNET)
			.then((response: AxiosResponse) => {
				this.interfaces = response.data;
			});
		this.getConnections();
	}

	/**
	 * Retrieves existing network connections
	 */
	private getConnections(): Promise<void> {
		return NetworkConnectionService.list(ConnectionType.ETHERNET)
			.then((response: AxiosResponse) => {
				this.connections = response.data;
			});
	}

	/**
	 * Returns a color to use in interface state badge
	 * @param {InterfaceState} status Interface status
	 * @returns {string} Badge color string
	 */
	private getStatusBadgeColor(status: InterfaceState): string {
		switch (status) {
			case InterfaceState.CONNECTED:
				return 'success';
			case InterfaceState.FAILED:
				return 'danger';
			default:
				return 'warning';
		}
	}	
}
</script>
