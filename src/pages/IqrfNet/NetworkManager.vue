<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	<div>
		<h1>{{ $t('iqrfnet.networkManager.title') }}</h1>
		<v-row>
			<v-col lg='6'>
				<v-card>
					<v-tabs v-model='activeTab'>
						<v-tab>
							{{ $t("iqrfnet.networkManager.iqmesh") }}
						</v-tab>
						<v-tab>
							{{ $t("iqrfnet.networkManager.autoNetwork.title") }}
						</v-tab>
						<v-tab>
							{{ $t("iqrfnet.networkManager.backupRestore") }}
						</v-tab>
						<v-tab v-if='daemon236'>
							{{ $t("iqrfnet.networkManager.otaUpload.title") }}
						</v-tab>
					</v-tabs>
					<v-tabs-items v-model='activeTab'>
						<v-tab-item :transition='false'>
							<BondingManager class='mb-5' @update-devices='updateDevices' />
							<DiscoveryManager @update-devices='updateDevices' />
						</v-tab-item>
						<v-tab-item :transition='false'>
							<AutoNetwork ref='autonetwork' @update-devices='updateDevices' />
						</v-tab-item>
						<v-tab-item :transition='false'>
							<Backup class='mb-5' />
							<Restore />
						</v-tab-item>
						<v-tab-item v-if='daemon236' :transition='false'>
							<OtaUpload />
						</v-tab-item>
					</v-tabs-items>
				</v-card>
			</v-col>
			<v-col lg='6'>
				<DevicesInfo ref='devs' />
			</v-col>
		</v-row>
	</div>
</template>

<script lang='ts'>
import {Component, Vue, Watch} from 'vue-property-decorator';
import {mapGetters} from 'vuex';
import {versionHigherEqual} from '@/helpers/versionChecker';
import Backup from '@/components/IqrfNet/NetworkManager/Backup.vue';
import Restore from '@/components/IqrfNet/NetworkManager/Restore.vue';
import BondingManager from '@/components/IqrfNet/NetworkManager/BondingManager.vue';
import DevicesInfo from '@/components/IqrfNet/NetworkManager/DevicesInfo.vue';
import DiscoveryManager from '@/components/IqrfNet/NetworkManager/DiscoveryManager.vue';
import AutoNetwork from '@/components/IqrfNet/NetworkManager/AutoNetwork.vue';
import OtaUpload from '@/components/IqrfNet/NetworkManager/OtaUpload.vue';
import {ToastOptions} from 'vue-toast-notification';

@Component({
	components: {
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
			daemonVersion: 'daemonClient/getVersion',
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
	private activeTab = 0;

	/**
	 * @var {boolean} daemon236 Indicates that Daemon version is 2.3.6 or higher
	 */
	private daemon236 = false;

	/**
	 * Daemon version computed property watcher to re-render elements dependent on version
	 */
	@Watch('daemonVersion')
	private updateDaemonVersion(): void {
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
