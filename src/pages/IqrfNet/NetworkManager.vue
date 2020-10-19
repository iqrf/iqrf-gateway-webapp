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
						<CTab title='Backup/Restore'>
							<Backup />
							<Restore />
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
import Backup from '../../components/IqrfNet/Backup';
import Restore from '../../components/IqrfNet/Restore';
import BondingManager from '../../components/IqrfNet/BondingManager';
import DevicesInfo from '../../components/IqrfNet/DevicesInfo';
import DiscoveryManager from '../../components/IqrfNet/DiscoveryManager';
import AutoNetwork from '../../components/IqrfNet/AutoNetwork';

@Component({
	components: {
		CCard,
		CTab,
		CTabs,
		AutoNetwork,
		Backup,
		BondingManager,
		DevicesInfo,
		DiscoveryManager,
		Restore,
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
