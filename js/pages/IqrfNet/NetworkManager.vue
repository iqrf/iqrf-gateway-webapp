<template>
	<CRow>
		<CCol lg='6'>
			<BondingManager @update-devices='updateDevices' />
			<DiscoveryManager @update-devices='updateDevices' />
		</CCol>
		<CCol lg='6'>
			<DevicesInfo ref='devs' />
		</CCol>
	</CRow>
</template>

<script>
import BondingManager from '../../components/IqrfNet/BondingManager';
import DevicesInfo from '../../components/IqrfNet/DevicesInfo';
import DiscoveryManager from '../../components/IqrfNet/DiscoveryManager';

export default {
	name: 'NetworkManager',
	components: {
		BondingManager,
		DevicesInfo,
		DiscoveryManager,
	},
	data() {
		return {};
	},
	methods: {
		updateDevices() {
			this.$refs.devs.getBondedDevices();
			this.$refs.devs.getDiscoveredDevices();
			this.$refs.devs.submitFrcPing();
		},
	},
	metaInfo: {
		title: 'iqrfnet.networkManager.title',
	},
};
</script>
