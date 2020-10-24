<template>
	<div>
		<h1>{{ $t('iqrfnet.networkManager.title') }}</h1>
		<CRow>
			<CCol lg='6'>
				<CCard>
					<CTabs variant='tabs' :active-tab='activeTab'>
						<CTab title='IQMESH'>
							<BondingManager @update-devices='updateDevices' />
							<DiscoveryManager @update-devices='updateDevices' />
						</CTab>
						<CTab title='AutoNetwork'>
							<AutoNetwork ref='autonetwork' @update-devices='updateDevices' />
						</CTab>
					</CTabs>
				</CCard>
			</CCol>
			<CCol lg='6'>
				<DevicesInfo ref='devs' @notify-autonetwork='getVersion' />
			</CCol>
		</CRow>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CTab, CTabs} from '@coreui/vue/src';
import BondingManager from '../../components/IqrfNet/BondingManager.vue';
import DevicesInfo from '../../components/IqrfNet/DevicesInfo.vue';
import DiscoveryManager from '../../components/IqrfNet/DiscoveryManager.vue';
import AutoNetwork from '../../components/IqrfNet/AutoNetwork.vue';

@Component({
	components: {
		CCard,
		CTab,
		CTabs,
		AutoNetwork,
		BondingManager,
		DevicesInfo,
		DiscoveryManager,
	},
	metaInfo: {
		title: 'iqrfnet.networkManager.title',
	},
})

/**
 * Network manager page component
 */
export default class NetworkManager extends Vue {
	/**
	 * @const {number} activeTab Default active tab
	 */
	private activeTab = 0
	
	/**
	 * Retrieves Daemon version on notify-autonetwork event emitted by successful FRC ping
	 */
	private getVersion(): void {
		(this.$refs.autonetwork as AutoNetwork).getVersion();
	}

	/**
	 * Refreshes table of devices on update-devices event emitted by a bonding or discovery action
	 */
	private updateDevices(): void {
		(this.$refs.devs as DevicesInfo).getBondedDevices();
	}

}
</script>
