<template>
	<div>
		<h1>{{ $t('iqrfnet.networkManager.title') }}</h1>
		<CRow>
			<CCol lg='6'>
				<CCard>
					<CTabs variant='tabs' :active-tab='activeTab'>
						<CTab :title='$t("iqrfnet.networkManager.iqmesh")'>
							<BondingManager @update-devices='updateDevices' />
							<DiscoveryManager @update-devices='updateDevices' />
						</CTab>
						<CTab v-if='daemon230' :title='$t("iqrfnet.networkManager.autoNetwork.title")'>
							<AutoNetwork ref='autonetwork' @update-devices='updateDevices' />
						</CTab>
						<CTab v-if='daemon230' :title='$t("iqrfnet.networkManager.backupRestore")'>
							<Backup />
							<Restore />
						</CTab>
						<CTab v-if='daemon236' :title='$t("iqrfnet.networkManager.otaUpload.title")'>
							<OtaUpload />
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
import {versionHigherEqual} from '../../helpers/versionChecker';
import Backup from '../../components/IqrfNet/Backup.vue';
import Restore from '../../components/IqrfNet/Restore.vue';
import BondingManager from '../../components/IqrfNet/BondingManager.vue';
import DevicesInfo from '../../components/IqrfNet/DevicesInfo.vue';
import DiscoveryManager from '../../components/IqrfNet/DiscoveryManager.vue';
import AutoNetwork from '../../components/IqrfNet/AutoNetwork.vue';
import OtaUpload from '../../components/IqrfNet/OtaUpload.vue';
import {ToastOptions} from 'vue-toast-notification';

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
		OtaUpload,
		Restore,
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
	 * @var {boolean} daemon230 Indicates that Daemon version is 2.3.0 or higher
	 */
	private daemon230 = false;

	/**
	 * @var {boolean} daemon236 Indicates that Daemon version is 2.3.6 or higher
	 */
	private daemon236 = false;

	/**
	 * Daemon version computed property watcher to re-render elements dependent on version
	 */
	@Watch('daemonVersion')
	private updateDaemonVersion(): void {
		if (versionHigherEqual('2.3.0')) {
			this.daemon230 = true;
		}
		if (versionHigherEqual('2.3.6')) {
			this.daemon236 = true;
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
	 * @param {ToastOptions} message Success toast message passed from other components for devices info grid to show
	 */
	private updateDevices(message: ToastOptions): void {
		(this.$refs.devs as DevicesInfo).getBondedDevices(message);
	}

}
</script>
