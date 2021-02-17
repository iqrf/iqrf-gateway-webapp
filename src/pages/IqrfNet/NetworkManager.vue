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
							<AutoNetwork v-if='daemonHigher230' ref='autonetwork' @update-devices='updateDevices' />
							<VersionAlert v-else />
						</CTab>
						<CTab title='Backup/Restore'>
							<div v-if='daemonHigher230'>
								<Backup />
								<Restore />
							</div>
							<VersionAlert v-else />
						</CTab>
					</CTabs>
				</CCard>
			</CCol>
			<CCol lg='6'>
				<DevicesInfo ref='devs' />
			</CCol>
		</CRow>
	</div>
</template>

<script lang='ts'>
import {Component, Vue, Watch} from 'vue-property-decorator';
import {CCard, CTab, CTabs} from '@coreui/vue/src';
import {mapGetters} from 'vuex';
import {versionHigherThan} from '../../helpers/versionChecker';
import Backup from '../../components/IqrfNet/Backup.vue';
import Restore from '../../components/IqrfNet/Restore.vue';
import BondingManager from '../../components/IqrfNet/BondingManager.vue';
import DevicesInfo from '../../components/IqrfNet/DevicesInfo.vue';
import DiscoveryManager from '../../components/IqrfNet/DiscoveryManager.vue';
import AutoNetwork from '../../components/IqrfNet/AutoNetwork.vue';
import VersionAlert from '../../components/IqrfNet/VersionAlert.vue';

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
		VersionAlert,
	},
	computed: {
		...mapGetters({
			daemonVersion: 'daemonVersion',
		}),
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
	 * @var {boolean} daemonHigher230 Indicates whether Daemon version is 2.3.0 or higher
	 */
	private daemonHigher230 = false;

	/**
	 * Daemon version computed property watcher to re-render elements dependent on version
	 */
	@Watch('daemonVersion')
	private updateDaemonVersion(): void {
		if (versionHigherThan('2.3.0')) {
			this.daemonHigher230 = true;
		}
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.updateDaemonVersion();
	}

	/**
	 * Refreshes table of devices on update-devices event emitted by a bonding or discovery action
	 * @param {string} message Success toast message passed from other components for devices info grid to show
	 */
	private updateDevices(message: string): void {
		(this.$refs.devs as DevicesInfo).getBondedDevices(message);
	}

}
</script>
