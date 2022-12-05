<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
		<CCard>
			<CCardHeader>
				{{ $t('iqrfnet.trUpload.osUpload.title') }}
			</CCardHeader>
			<CCardBody>
				<CElementCover
					v-if='loadFailed'
					style='z-index: 1;'
					:opacity='0.85'
				>
					{{ $t('iqrfnet.trUpload.osUpload.messages.fetchFailed') }}
				</CElementCover>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='upgradeOs()'>
						<fieldset :disabled='loadFailed'>
							<p>
								<span v-if='currentOsVersion !== "" && currentOsBuild !== ""'>
									<b>{{ $t('iqrfnet.trUpload.osUpload.form.current') }}</b> {{ prettyVersion(currentOsVersion) + ' (' + currentOsBuild + ')' }}
								</span>
							</p>
							<div v-if='selectVersions.length > 0'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: "iqrfnet.trUpload.osUpload.errors.version"
									}'
								>
									<CSelect
										:value.sync='osVersion'
										:label='$t("iqrfnet.trUpload.osUpload.form.version")'
										:placeholder='$t("iqrfnet.trUpload.osUpload.errors.version")'
										:options='selectVersions'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<CButton
									color='primary'
									type='submit'
									:disabled='invalid'
								>
									{{ $t('forms.upload') }}
								</CButton>
							</div>
							<CAlert
								v-if='selectVersions.length === 0 && currentOsVersion !== "" && currentOsBuild !== ""'
								color='success'
							>
								{{ $t('iqrfnet.trUpload.osUpload.messages.newest') }}
							</CAlert>
						</fieldset>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CModal, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import IqrfService from '@/services/IqrfService';
import ServiceService from '@/services/ServiceService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/coreui';
import {IqrfOsUpgrade, UploadUtilFile, IqrfOsUpgradeFiles} from '@/interfaces/trUpload';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CModal,
		CSelect,
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
	 * @var {Array<IOption>} selectVersions Array of available IQRF OS versions for CoreUI select
	 */
	private selectVersions: Array<IOption> = [];

	/**
	 * @var {number} trMcuType Module TR and MCU type
	 */
	private trMcuType = 0;

	/**
	 * @var {Array<IqrfOsUpgrade>} upgrades Array of possible IQRF OS upgrades
	 */
	private upgrades: Array<IqrfOsUpgrade> = [];

	/**
	 * @var {boolean} uploadError Indicates whether an upload error has occured
	 */
	private uploadError = false;

	/**
	 * @var {boolean} loadFailed Indicates whether OS upgrades fetch failed
	 */
	private loadFailed = false;

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
	public handleEnumResponse(response: any): void {
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
				this.$emit('loaded', {name: 'OS', success: false});
			});
	}

	/**
	 * Updates list of available IQRF OS upgrades for select
	 */
	private updateVersions(): void {
		const versions: Array<IOption> = [];
		for (let i = 0; i < this.upgrades.length; ++i) {
			const upgrade: IqrfOsUpgrade = this.upgrades[i];
			console.warn(upgrade);
			let label = upgrade.os.version + ' (' + upgrade.os.build;
			if (upgrade.os.attributes.beta) {
				label += ', Beta version';
			}
			if (upgrade.os.attributes.obsolete) {
				label += ', Obsolete';
			}
			label += '), DPA ' + upgrade.dpa.version;
			if (upgrade.dpa.attributes.beta || upgrade.dpa.attributes.obsolete) {
				label += ' (';
				if (upgrade.dpa.attributes.beta) {
					label += 'Beta version, ';
				}
				if (upgrade.dpa.attributes.obsolete) {
					label += 'Obsolete)';
				} else {
					label = label.substr(0, label.length - 2) + ')';
				}
			}
			versions.push({
				value: i,
				label: label,
			});
		}
		this.selectVersions = versions.sort().reverse();
		this.$emit('loaded', {name: 'OS', success: true});
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
		const data = {
			fromBuild: this.currentOsBuild,
			fromVersion: this.currentOsVersion,
			toVersion: upgrade.osVersion,
			toBuild: upgrade.os.build,
			dpa: dpaRaw,
			interface: this.interfaceType,
			trMcuType: this.trMcuType,
		};
		if (dpaRaw < '0400') {
			Object.assign(data, {rfMode: upgrade.dpa.version.endsWith('STD') ? 'STD' : 'LP'});
		}
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT',
			this.$t('iqrfnet.trUpload.osUpload.messages.gatewayUpload').toString()
		);
		IqrfService.getUpgradeFiles(data)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/UPDATE_TEXT',
					this.$t('iqrfnet.trUpload.osUpload.messages.gatewayUploadSuccess').toString()
				);
				this.upload(response.data);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'iqrfnet.trUpload.osUpload.messages.gatewayUploadFailed'));
	}

	/**
	 * Creates REST API request body to upgrade IQRF OS and executes upgrade.
	 * @param {IqrfOsUpgradeFiles} responseFiles Files needed to upgrade IQRF OS
	 */
	private upload(responseFiles: IqrfOsUpgradeFiles): void {
		const files: Array<UploadUtilFile> = [];
		for (const file of responseFiles.os.sort()) {
			files.push({name: file, type: 'OS'});
		}
		files.push({name: responseFiles.dpa, type: 'DPA'});
		this.stopDaemon().then(async () => {
			for (const file of files) {
				this.$store.commit('spinner/UPDATE_TEXT',
					this.$t('iqrfnet.trUpload.' + (file.type === 'OS' ? 'osUpload' : 'dpaUpload') + '.messages.trUpload').toString()
				);
				await IqrfService.uploader(file)
					.then(() => {
						this.$store.commit('spinner/UPDATE_TEXT',
							this.$t('iqrfnet.trUpload.' + (file.type === 'OS' ? 'osUpload' : 'dpaUpload') + '.messages.trUploadSuccess').toString()
						);
					})
					.catch((error: AxiosError) => {
						extendedErrorToast(error, 'iqrfnet.trUpload.' + (file.type === 'OS' ? 'osUpload' : 'dpaUpload') + '.messages.trUploadFailed');
						this.uploadError = true;
					});
				if (this.uploadError) {
					break;
				}
			}
			if (!this.uploadError) {
				this.startDaemon();
			}
		});
	}

	/**
	 * Stops the IQRF Daemon service before upgrading OS
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private stopDaemon(): Promise<void> {
		return ServiceService.stop('iqrf-gateway-daemon')
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
		ServiceService.start('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$emit('os-upload');
			})
			.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.startFailed'));
	}
}
</script>
