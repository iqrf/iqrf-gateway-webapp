<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
			<v-card-title>{{ $t('iqrfnet.trUpload.dpaUpload.title') }}</v-card-title>
			<v-card-text>
				<v-overlay
					v-if='loadFailed'
					absolute
					:opacity='0.65'
				>
					{{ $t('iqrfnet.trUpload.dpaUpload.messages.dpaFetchFailure') }}
				</v-overlay>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<div v-if='versions.length > 0'>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='required'
								:custom-messages='{
									required: $t("iqrfnet.trUpload.dpaUpload.errors.version"),
								}'
							>
								<v-select
									v-model='version'
									:items='versions'
									:label='$t("iqrfnet.trUpload.dpaUpload.form.version")'
									:success='touched ? valid : null'
									:invalid-feedback='errors'
									:placeholder='$t("iqrfnet.trUpload.dpaUpload.errors.version")'
								/>
							</ValidationProvider>
							<v-btn
								color='primary'
								:disabled='invalid'
								@click='compareUploadedVersion'
							>
								{{ $t('forms.upload') }}
							</v-btn>
						</div>
						<v-alert
							v-if='versions.length === 0 && currentDpa.length > 0'
							type='warning'
							text
						>
							{{ $t('iqrfnet.trUpload.dpaUpload.messages.noUpgrades') }}
						</v-alert>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<DpaUpdateConfirmationModal
			v-model='showModal'
			:current-dpa='currentDpa'
			@upload='getDpaFile'
		/>
	</div>
</template>

<script lang='ts'>
import {ServiceService} from '@iqrf/iqrf-gateway-webapp-client/services';
import { Client as RepositoryClient } from '@iqrf/iqrf-repository-client';
import { OsDpaService } from '@iqrf/iqrf-repository-client/services';
import { OsDpa } from '@iqrf/iqrf-repository-client/types';
import {AxiosError, AxiosResponse} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {MutationPayload} from 'vuex';
import {Component, Vue} from 'vue-property-decorator';
import DpaUpdateConfirmationModal from '@/components/IqrfNet/TrUpload/DpaUpdateConfirmationModal.vue';

import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import DpaService, {RFMode} from '@/services/IqrfRepository/OsDpaService';
import IqrfNetService from '@/services/IqrfNetService';
import IqrfService from '@/services/IqrfService';
import {useApiClient} from '@/services/ApiClient';
import {useRepositoryClient} from '@/services/IqrfRepositoryClient';

interface DpaVersions {
	text: string
	value: string
}

@Component({
	components: {
		DpaUpdateConfirmationModal,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Dpa updater card for TrUpload component
 */
export default class DpaUpdater extends Vue {
	/**
	 * @constant {number} address Network address of device
	 */
	private address = 0;

	/**
	 * @var {string} currentDpa Current version of DPA
	 */
	private currentDpa = '';

	/**
	 * @var {string|null} interfaceType Active IQRF communication interface
	 */
	private interfaceType: string|null = null;

	/**
	 * @var {boolean} loadFailed Indicates whether DPA upgrades fetch failed
	 */
	private loadFailed = false;

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null;

	/**
	 * @var {string} osBuild IQRF OS build
	 */
	private osBuild = '0000';

	/**
	 * @var {number|null} trType Transceiver type identifier
	 */
	private trType: number|null = null;

	/**
   * @property {ServiceService} serviceService Service service
   * @private
   */
	private serviceService: ServiceService = useApiClient().getServiceService();

	/**
	 * @property {RepositoryClient} repositoryClient IQRF Repository client
   * @private
   */
	private repositoryClient!: RepositoryClient;

	/**
	 * @property {OsDpaService} osDpaService IQRF OS & DPA service
   * @private
   */
	private osDpaService!: OsDpaService;

	/**
	 * @var {boolean} showModal Controls whether DPA upload modal is shown
	 */
	private showModal = false;

	/**
	 * @var {string} version Currently selected version of DPA
	 */
	private version = '';

	/**
	 * @var {Array<DpaVersions>} versions Array of available DPA versions to update to
	 */
	private versions: Array<DpaVersions> = [];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	async created(): Promise<void> {
		this.repositoryClient = await useRepositoryClient();
		this.osDpaService = this.repositoryClient.getOsDpaService();
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_EnumerateDevice') {
				if (mutation.payload.data.status === 0) {
					this.interfaceType = mutation.payload.data.rsp.osRead.flags.interfaceType;
					this.$emit('loaded', {name: 'DPA', success: true});
				} else {
					this.$emit('loaded', {name: 'DPA', success: false});
				}
			}
		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Performs device enumeration
	 */
	private getDeviceEnumeration(): void {
		IqrfNetService.enumerateDevice(this.address, 60000, 'iqrfnet.enumeration.messages.failure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * EmbedOs info response handler
	 */
	public handleEnumResponse(response): void {
		this.osBuild = response.osRead.osBuild ?? '0000';
		this.trType = response.osRead.trMcuType.value;
		this.currentDpa = response.peripheralEnumeration.dpaVer.replaceAll(' ', '0');
		this.osDpaService.list({osBuild: this.osBuild})
			.then((versions: OsDpa[]) => {
				const fetchedVersions: Array<DpaVersions> = [];
				for (const version of versions) {
					const dpaVer = Number.parseInt(version.dpa.version.replace(/\./g, ''));
					if (dpaVer < 400) {
						fetchedVersions.push({
							value: version.dpa.version + '-' + RFMode.LP,
							text: version.dpa.version + ', ' + RFMode.LP + ' RF mode'
						});
						fetchedVersions.push({
							value: version.dpa.version + '-' + RFMode.STD,
							text: version.dpa.version + ', ' + RFMode.STD + ' RF mode'
						});
					} else {
						fetchedVersions.push({
							value: version.dpa.version,
							text: version.dpa.version,
						});
					}
				}
				fetchedVersions.forEach(item => {
					if (this.currentDpa === item.value) {
						Object.assign(item, {text: item.text + ' (Current version)'});
					}
				});
				fetchedVersions.sort();
				fetchedVersions.reverse();
				this.versions = fetchedVersions;
				this.getDeviceEnumeration();
			})
			.catch(() => {
				this.$emit('loaded', {name: 'DPA', success: false});
			});
	}

	/**
	 * Updates list of DPA version to reflect changes made by upload
	 */
	private updateVersions(): void {
		for (const item of this.versions) {
			if (item.text.endsWith('(Current version)')) {
				item.text = item.text.slice(0, -18);
			}
			if (item.value === this.version) {
				item.text += ' (Current version)';
			}
		}
		this.currentDpa = this.version;
	}

	/**
	 * Displays a modal window if new version is the same as current version, otherwise executes upload
	 */
	private compareUploadedVersion(): void {
		if (this.version.length === 0) {
			return;
		}
		if (this.currentDpa === this.version) {
			this.showModal = true;
		} else {
			this.getDpaFile();
		}
	}

	/**
	 * Retrieves DPA file to upload
	 */
	private getDpaFile(): void {
		if (this.version === '') {
			return;
		}
		const request = {
			'interfaceType': this.interfaceType,
			'osBuild': this.osBuild,
			'trSeries': this.trType,
		};
		const rawVersion = this.version.split('-')[0].replace(/\./g, '').padStart(4, '0');
		if (this.version.endsWith('-STD')) {
			Object.assign(request, {'dpa': rawVersion});
			Object.assign(request, {'rfMode': RFMode.STD});
		} else if (this.version.endsWith('-LP')) {
			Object.assign(request, {'dpa': rawVersion});
			Object.assign(request, {'rfMode': RFMode.LP});
		} else {
			Object.assign(request, {'dpa': rawVersion});
		}
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT',
			this.$t('iqrfnet.trUpload.dpaUpload.messages.gatewayUpload').toString()
		);
		DpaService.getDpaFile(request)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/UPDATE_TEXT',
					this.$t('iqrfnet.trUpload.dpaUpload.messages.gatewayUploadSuccess').toString()
				);
				this.stopDaemon(response.data.fileName);

			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'iqrfnet.trUpload.dpaUpload.messages.gatewayUploadFailed'));
	}

	/**
	 * Attempts to fetch a DPA plugin file and store it.
	 * If the fetch is successful, the name of the file is returned in response and a NativeUpload daemon api call is executed.
	 */
	private upload(fileName: string): void {
		this.$store.commit('spinner/UPDATE_TEXT',
			this.$t('iqrfnet.trUpload.dpaUpload.messages.trUpload').toString()
		);
		IqrfService.uploader({name: fileName, type: 'DPA'})
			.then(() => {
				this.$store.commit('spinner/UPDATE_TEXT',
					this.$t('iqrfnet.trUpload.dpaUpload.messages.trUploadSuccess').toString()
				);
				this.startDaemon();
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'iqrfnet.trUpload.dpaUpload.messages.trUploadFailed'));
	}

	/**
	 * Stops the IQRF Daemon service before upgrading OS
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private stopDaemon(fileName: string): Promise<void> {
		return this.serviceService.stop('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/UPDATE_TEXT',
					this.$t('service.iqrf-gateway-daemon.messages.stop').toString()
				);
				this.upload(fileName);
			})
			.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.stopFailed'));
	}

	/**
	 * Starts the IQRF Daemon service upon successful OS upgrade
	 */
	private startDaemon(): void {
		this.serviceService.start('iqrf-gateway-daemon')
			.then(() => {
				this.updateVersions();
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.startFailed'));
	}
}
</script>

