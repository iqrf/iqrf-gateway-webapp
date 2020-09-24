<template>
	<CRow>
		<CCol lg='6'>
			<CTabs variant='tabs' :active-tab='activeTab'>
				<CTab title='IQMESH'>
					<BondingManager @update-devices='updateDevices' />
				</CTab>
				<CTab title='AutoNetwork'>
					<AutoNetwork @update-devices='updateDevices' />
				</CTab>
			</CTabs>
			<DiscoveryManager @update-devices='updateDevices' />
		</CCol>
		<CCol lg='6'>
			<DevicesInfo ref='devs' />
		</CCol>
	</CRow>
</template>

<script>
import {CTab, CTabs} from '@coreui/vue/src';
import BondingManager from '../../components/IqrfNet/BondingManager';
import DevicesInfo from '../../components/IqrfNet/DevicesInfo';
import DiscoveryManager from '../../components/IqrfNet/DiscoveryManager';
import AutoNetwork from '../../components/IqrfNet/AutoNetwork';

export default {
	name: 'NetworkManager',
	components: {
		CTab,
		CTabs,
		AutoNetwork,
		BondingManager,
		DevicesInfo,
		DiscoveryManager,
	},
	data() {
		return {
			activeTab: 0,
		};
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

<style scoped>

.tabs {
	border-bottom: 0;
}

</style>
