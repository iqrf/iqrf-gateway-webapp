<template>
	<CRow>
		<CCol md='6'>
			<BondingManager @update-devices='updateDevices' />
			<DiscoveryManager @update-devices='updateDevices' />
		</CCol>
		<CCol md='6'>
			<DevicesInfo ref='devs' />
		</CCol>
	</CRow>
</template>

<script>

import BondingManager from './BondingManager';
import DevicesInfo from './DevicesInfo';
import DiscoveryManager from './DiscoveryManager';

export default {
	name: 'NetworkManager',
	components: {
		BondingManager,
		DevicesInfo,
		DiscoveryManager
	},
	data() {
		return {
			
		};
	},
	methods: {
		updateDevices() {
			this.$refs.devs.getBondedDevices();
			this.$refs.devs.getDiscoveredDevices();
			this.$refs.devs.submitFrcPing();
		}
	}
};
</script>

<style>

</style>