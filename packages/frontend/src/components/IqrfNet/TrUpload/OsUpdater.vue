<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.trUpload.osUpload.title') }}
			</v-card-title>
			<v-card-text>
				<v-overlay
					v-if='loadFailed'
					absolute
					:opacity='0.65'
				>
					{{ $t('iqrfnet.trUpload.osUpload.messages.fetchFailed') }}
				</v-overlay>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<p v-if='currentOsVersion !== "" && currentOsBuild !== ""'>
							<span>
								<strong>{{ $t('iqrfnet.trUpload.osUpload.form.current') }}</strong> {{ prettyVersion(currentOsVersion) + ' (' + currentOsBuild + ')' }}
							</span>
						</p>
						<v-alert
							v-if='!loadFailed && selectVersions.length === 0'
							color='success'
							text
							outlined
						>
							{{ $t('iqrfnet.trUpload.osUpload.messages.newest') }}
						</v-alert>
						<div v-else-if='!loadFailed && selectVersions.length > 0'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("iqrfnet.trUpload.osUpload.errors.version"),
								}'
							>
								<v-select
									v-model='osVersion'
									:items='selectVersions'
									:label='$t("iqrfnet.trUpload.osUpload.form.version")'
									:placeholder='$t("iqrfnet.trUpload.osUpload.errors.version")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<v-btn
								color='primary'
								:disabled='invalid'
								@click='upgradeOs'
							>
								{{ $t('forms.upload') }}
							</v-btn>
						</div>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {ServiceService} from '@iqrf/iqrf-gateway-webapp-client/services';
import {AxiosError, AxiosResponse} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';

import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import IqrfService from '@/services/IqrfService';

import {IAvailableOsUpgrade, IOsUpgradeParams} from '@/interfaces/trUpload';
import {ISelectItem} from '@/interfaces/Vuetify';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * IQRF OS update component for Coordinator Upload
 */
export default class OsUpdater extends Vue {
	/**
	 * @var {string} currentOsBuild Currently uploaded OS build
	 */
	private currentOsBuild = '';

	/**
	 * @var {string} currentOsVersion Currently uploaded OS version
	 */
	private currentOsVersion = '';

	/**
	 * @var {string} interfaceType Used IQRF interface
	 */
	private interfaceType = '';

	/**
	 * @var {number} osVersion Selected version of IQRF OS
	 */
	private osVersion: number|null = null;

	/**
	 * @var {Array<ISelectItem>} selectVersions IQRF OS version select options
	 */
	private selectVersions: Array<ISelectItem> = [];

	/**
	 * @var {number} trMcuType Module TR and MCU type
	 */
	private trMcuType = 0;

	/**
	 * @var {Array<IAvailableOsUpgrade>} upgrades Array of possible IQRF OS upgrades
	 */
	private upgrades: Array<IAvailableOsUpgrade> = [];

	/**
	 * @var {boolean} loadFailed Indicates whether OS upgrades fetch failed
	 */
	private loadFailed = false;

	/**
   * @property {ServiceService} serviceService Service service
   * @private
   */
	private serviceService: ServiceService = useApiClient().getServiceService();

	/**
	 * Vue lifecycle hook created
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Handles device enumeration response
	 */
	public handleEnumResponse(response): void {
		this.currentOsBuild = response.osRead.osBuild;
		const osVersion = response.osRead.osVersion.split('.').join('');
		this.currentOsVersion = osVersion.charAt(0) + '0' + osVersion.charAt(2);
		this.trMcuType = response.osRead.trMcuType.value;
		const flags = ('00000000' + response.osRead.flags.value.toString(2)).slice(-8);
		if (flags[3] === '0') {
			this.interfaceType = flags[6] === '0' ? 'SPI' : 'UART';
		}
		const data = {
			build: this.currentOsBuild,
			version: this.currentOsVersion,
			mcuType: parseInt(this.trMcuType.toString(2).slice(-3), 2),
		};
		this.getOsUpgrades(data);
	}

	/**
	 * Retrieves list of IQRF OS patches from database
	 */
	private getOsUpgrades(data: Record<string, string|number>): void {
		IqrfService.getUpgrades(data)
			.then((response: AxiosResponse) => {
				this.upgrades = response.data;
				this.updateVersions();
			})
			.catch(() => {
				this.loadFailed = true;
			});
	}

	/**
	 * Updates list of available IQRF OS upgrades for select
	 */
	private updateVersions(): void {
		const versions: Array<ISelectItem> = [];
		for (let i = 0; i < this.upgrades.length; ++i) {
			const upgrade: IAvailableOsUpgrade = this.upgrades[i];
			let text = upgrade.os.version + ' (' + upgrade.os.build;
			if (upgrade.os.attributes.beta) {
				text += ', Beta version';
			}
			if (upgrade.os.attributes.obsolete) {
				text += ', Obsolete';
			}
			text += '), DPA ' + upgrade.dpa.version;
			if (upgrade.dpa.attributes.beta || upgrade.dpa.attributes.obsolete) {
				text += ' (';
				if (upgrade.dpa.attributes.beta) {
					text += 'Beta version, ';
				}
				if (upgrade.dpa.attributes.obsolete) {
					text += 'Obsolete)';
				} else {
					text = text.substring(0, text.length - 2) + ')';
				}
			}
			if ((upgrade.dpa.rfMode ?? null) !== null) {
				text += ', ' + upgrade.dpa.rfMode;
			}
			versions.push({
				value: i,
				text: text,
			});
		}
		versions.sort();
		versions.reverse();
		this.selectVersions = versions;
	}

	/**
	 * Converts OS version string to string with major and minor version
	 * @param {string} version OS version string
	 * @returns {string} Updated version string
	 */
	private prettyVersion(version: string): string {
		return version.charAt(0) + '.' + version.substring(1, version.length);
	}

	/**
	 * Creates REST API request body to fetch files needed to upgrade IQRF OS,
	 * then executes upgrade using the IQRF Upload Utility via REST API.
	 */
	private upgradeOs(): void {
		if (this.osVersion === null) {
			return;
		}
		const upgrade = this.upgrades[this.osVersion];
		const dpaRaw = upgrade.dpa.version.split('.').join('').padStart(4, '0');
		const data: IOsUpgradeParams = {
			fromBuild: this.currentOsBuild,
			toBuild: upgrade.os.build,
			dpa: dpaRaw,
			interface: this.interfaceType,
			trMcuType: this.trMcuType,
		};
		if (dpaRaw < '0400') {
			Object.assign(data, {rfMode: upgrade.dpa.rfMode});
		}
		this.$store.commit('spinner/SHOW');
		this.stopDaemon().then(async () => {
			this.$store.commit(
				'spinner/UPDATE_TEXT',
				this.$t('iqrfnet.trUpload.osUpload.messages.upgrade').toString()
			);
			IqrfService.upgradeOs(data)
				.then(async () => {
					await this.startDaemon();
					this.$toast.success(
						this.$t('iqrfnet.trUpload.osUpload.messages.upgradeSuccess').toString()
					);
				})
				.catch((error: AxiosError) => extendedErrorToast(error, 'iqrfnet.trUpload.osUpload.messages.upgradeFailed'));
		});
	}

	/**
	 * Stops the IQRF Daemon service before upgrading OS
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private stopDaemon(): Promise<void> {
		return this.serviceService.stop('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/UPDATE_TEXT',
					this.$t('service.iqrf-gateway-daemon.messages.stop').toString()
				);
			})
			.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.stopFailed'));
	}

	/**
	 * Starts the IQRF Daemon service upon successful OS upgrade
	 */
	private startDaemon(): void {
		this.serviceService.start('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$emit('os-upload');
			})
			.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.startFailed'));
	}
}
</script>
