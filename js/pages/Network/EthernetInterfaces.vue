<template>
	<div>
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

<script>
import {CBadge, CCard, CCardBody, CCardHeader} from '@coreui/vue/src';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceState, InterfaceType} from '../../services/NetworkInterfaceService';
import EthernetConnections from '../../components/EthernetConnections';

export default {
	name: 'EthernetInterfaces',
	components: {
		EthernetConnections,
		CBadge,
		CCard,
		CCardBody,
		CCardHeader,
	},
	data() {
		return {
			connections: [],
			interfaces: [],
		};
	},
	created() {
		NetworkInterfaceService.list(InterfaceType.ETHERNET)
			.then((response) => {
				this.interfaces = response.data;
			});
		this.getConnections();
	},
	methods: {
		getConnections() {
			return NetworkConnectionService.list(ConnectionType.ETHERNET)
				.then((response) => {
					this.connections = response.data;
				});
		},
		getStatusBadgeColor(status) {
			switch (status) {
				case InterfaceState.CONNECTED:
					return 'success';
				case InterfaceState.FAILED:
					return 'danger';
				default:
					return 'warning';
			}
		},
	},
};
</script>
