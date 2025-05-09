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
	<v-card flat tile>
		<v-card-title>{{ $t('iqrfnet.networkManager.backupRestore.backup.title') }}</v-card-title>
		<v-card-text>
			<ValidationObserver v-slot='{invalid}'>
				<v-form @submit.prevent='backupDevice'>
					<v-select
						v-model='target'
						:label='$t("iqrfnet.networkManager.backupRestore.backup.form.target")'
						:items='selectOptions'
						:placeholder='$t("iqrfnet.networkManager.backupRestore.backup.form.messages.select")'
					/>
					<ValidationProvider
						v-if='target === "node"'
						v-slot='{errors, touched, valid}'
						rules='required|integer|between:1,239'
						:custom-messages='{
							integer: $t("iqrfnet.networkManager.backupRestore.backup.form.messages.address"),
							between: $t("iqrfnet.networkManager.backupRestore.backup.form.messages.address"),
							required: $t("iqrfnet.networkManager.backupRestore.backup.form.messages.address"),
						}'
					>
						<v-text-field
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("forms.fields.address")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<v-btn
						type='submit'
						color='primary'
						:disabled='invalid'
					>
						{{ $t('forms.backup') }}
					</v-btn>
				</v-form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {VersionIqrfGatewayWebapp} from '@iqrf/iqrf-gateway-webapp-client/types';
import {saveAs} from 'file-saver';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';
import {MutationPayload} from 'vuex';

import {NetworkTarget} from '@/enums/IqrfNet/network';

import IqrfNetService from '@/services/IqrfNetService';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import {IBackupData} from '@/interfaces/DaemonApi/Iqmesh/Backup';
import {ISelectItem} from '@/interfaces/Vuetify';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider
	},
})

/**
 * IQMESH Backup component card
 */
export default class Backup extends Vue {
	/**
	 * @var {string} msgId Daemon api message id
	 */
	private msgId = '';

	/**
	 * @var {number} address Address of device to backup
	 */
	private address = 1;

	/**
	 * @var {Array<IBackupData>} deviceData Array of device backup data
	 */
	private deviceData: Array<IBackupData> = [];

	/**
	 * @var {Array<number>} offlineDevices Array of devices offline during the backup process
	 */
	private offlineDevices: Array<number> = [];

	/**
	 * @var {Array<ISelectItem>} selectOptions Network target select options
	 */
	private selectOptions: Array<ISelectItem> = [
		{
			value: NetworkTarget.COORDINATOR,
			text: this.$t('forms.fields.coordinator').toString(),
		},
		{
			value: NetworkTarget.NODE,
			text: this.$t('iqrfnet.networkManager.backupRestore.backup.form.node').toString(),
		},
		{
			value: NetworkTarget.NETWORK,
			text: this.$t('iqrfnet.networkManager.backupRestore.backup.form.network').toString(),
		}
	];

	/**
	 * @var {string} target Backup target type
	 */
	private target = NetworkTarget.COORDINATOR;

	/**
	 * @var {string} webappVersion IQRF GW Webapp version
	 */
	private webappVersion = 'unknown';

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONERROR' ||
				mutation.type === 'daemonClient/SOCKET_ONCLOSE') {
				if (this.$store.getters['spinner/isEnabled']) {
					this.$store.commit('spinner/HIDE');
				}
				return;
			}
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			if (mutation.payload.mType === 'iqmeshNetwork_Backup') {
				this.handleBackupResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'messageError') {
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				this.$toast.error(
					this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
				);
			}
		});
	}

	mounted(): void {
		useApiClient().getVersionService().getWebapp()
			.then((response: VersionIqrfGatewayWebapp) => this.webappVersion = response.version)
			.catch(() => this.webappVersion = '');
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Backup concluding method, hides spinner, removes message id, generates backup file and toast message
	 */
	private concludeBackup() {
		this.$store.commit('spinner/HIDE');
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.generateBackupFile();
		this.backupSuccessToast();
	}

	/**
	 * Generates backup finished toast message depending on the state of devices in network
	 */
	private backupSuccessToast(): void {
		if (this.offlineDevices.length === 0) {
			let message: string;
			if (this.target === NetworkTarget.COORDINATOR) {
				message = this.$t('iqrfnet.networkManager.backupRestore.backup.messages.coordinatorSuccess').toString();
			} else if (this.target === NetworkTarget.NODE) {
				message = this.$t('iqrfnet.networkManager.backupRestore.backup.messages.nodeSuccess', {deviceAddr: this.address}).toString();
			} else {
				message = this.$t('iqrfnet.networkManager.backupRestore.backup.messages.networkSuccess').toString();
			}
			this.$toast.success(message);
		} else {
			this.$toast.info(
				this.$t('iqrfnet.networkManager.backupRestore.backup.messages.networkPartialSuccess', {devices: this.offlineDevices.join(', ')}).toString()
			);
		}
	}

	/**
	 * Backup response message handler
	 * @param data Daemon API response
	 */
	private handleBackupResponse(data): void {
		if (data.status === 0) { // no error detected
			this.deviceData.push(data.rsp.devices[0]);
			if (data.rsp.progress !== 100) { // backup process not finished
				this.$store.commit('spinner/UPDATE_TEXT', this.backupProgress(data));
				return;
			}
			this.concludeBackup();
			return;
		}

		if (this.target === NetworkTarget.COORDINATOR) {
			this.$store.commit('spinner/HIDE');
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$toast.error(
				this.$t('forms.messages.coordinatorOffline').toString()
			);
			return;
		} else if (this.target === NetworkTarget.NODE) {
			this.$store.commit('spinner/HIDE');
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (data.status === -1 || data.statusStr.includes('ERROR_TIMEOUT')) { // node device offline
				this.$toast.error(
					this.$t('forms.messages.deviceOffline', {address: this.address}).toString()
				);
			} else if (data.status === 8 || data.statusStr.includes('ERROR_NADR')) {
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
			}
			return;
		} else {
			if (data.status === -1 || data.statusStr.includes('ERROR_TIMEOUT')) { // node device is offline during network backup
				this.offlineDevices.push(data.rsp.devices[0].deviceAddr);
				if (data.rsp.progress === 100) {
					this.concludeBackup();
					return;
				}
			}
			this.$store.commit('spinner/UPDATE_TEXT', this.backupProgress(data));
		}
	}

	/**
	 * Performs device backup via daemon API
	 */
	private backupDevice(): void {
		this.deviceData = [];
		this.offlineDevices = [];
		const address = this.target === NetworkTarget.NODE ? this.address : 0;
		const wholeNetwork = this.target === 'network';
		const options = new DaemonMessageOptions(null);
		let message: string;
		if (this.target === NetworkTarget.COORDINATOR) {
			message = this.$t('iqrfnet.networkManager.backupRestore.backup.messages.coordinatorRunning').toString();
		} else if (this.target === NetworkTarget.NODE) {
			message = this.$t('iqrfnet.networkManager.backupRestore.backup.messages.nodeRunning', {deviceAddr: this.address}).toString();
		} else {
			message = this.$t('iqrfnet.networkManager.backupRestore.backup.messages.networkRunning', {progress: 0}).toString();
		}
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', message);
		IqrfNetService.backup(address, wholeNetwork, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Creates backup progress message for spinner
	 * @param response daemon api response
	 * @returns {string} Backup progress message
	 */
	private backupProgress(response): string {
		let message = this.$t('iqrfnet.networkManager.backupRestore.backup.messages.networkRunning', {progress: response.rsp.progress}).toString();
		const deviceAddr = response.rsp.devices[0].deviceAddr;
		if (response.status === 0) {
			if (deviceAddr === 0) {
				message += '\n' + this.$t('iqrfnet.networkManager.backupRestore.backup.messages.coordinatorSuccess', {deviceAddr: deviceAddr}).toString();
			} else {
				message += '\n' + this.$t('iqrfnet.networkManager.backupRestore.backup.messages.nodeSuccess', {deviceAddr: deviceAddr}).toString();
			}
		} else {
			message += '\n' + this.$t('iqrfnet.networkManager.backupRestore.backup.messages.nodeFailed', {deviceAddr: deviceAddr}).toString();
		}
		return message;
	}

	/**
	 * Generates backup file and prompts file save
	 */
	private generateBackupFile(): void {
		let fileContent = '[Backup]\nCreated=' + new Date().toLocaleString('en-GB').replace(/\//g, '.').replace(/,/g, '') + ' IQRF GW Webapp: ' + this.webappVersion + '\n\n';
		let fileName: string;
		if (this.target === NetworkTarget.COORDINATOR) {
			fileName = 'Coordinator_';
			fileContent += this.coordinatorBackup() + '\n';
		} else if (this.target === NetworkTarget.NODE) {
			fileName = 'Node_';
			fileContent += this.nodeBackup(0) + '\n';
		} else {
			fileName = 'Network_';
			fileContent += this.networkBackup();
		}
		fileName += this.deviceData[0].mid.toString(16).toUpperCase() + '_' + new Date().toISOString().slice(2, 10).replace(/-/g, '') + '.iqrfbkp';
		const blob = new Blob([fileContent], {type: 'text/plain;charset=utf-8'});
		saveAs(blob, fileName);
	}

	/**
	 * Creates a coordinator device type backup entry
	 * @returns {string} coordinator device backup data
	 */
	private coordinatorBackup(): string {
		const device = this.deviceData[0];
		let message = '[' + device.mid.toString(16).toUpperCase() + ']\n';
		message += 'Device=Coordinator\nVersion=' + this.getDpaVersion(device.dpaVer) + '\n';
		message += 'DataC=' + device.data.toUpperCase() + '\nAddress=' + device.deviceAddr + '\n';
		return message;
	}

	/**
	 * Creates a node device type backup entry
	 * @returns {string} node device backup data
	 */
	private nodeBackup(index: number): string {
		const device = this.deviceData[index];
		let message = '[' + device.mid.toString(16).toUpperCase() + ']\n';
		message += 'Device=Node\nVersion=' + this.getDpaVersion(device.dpaVer) + '\n';
		message += 'DataN=' + device.data.toUpperCase() + '\nAddress=' + device.deviceAddr + '\n';
		return message;
	}

	/**
	 * Creates a backup entry of the entire network
	 * @returns {string} network backup data
	 */
	networkBackup(): string {
		let message = this.coordinatorBackup() + '\n';
		for (let i = 1; i < this.deviceData.length; ++i) {
			message += this.nodeBackup(i) + '\n';
		}
		return message;
	}

	/**
	 * Converts DPA version from decimal number to string of hexadecimal characters
	 * @returns {string} dpa version hex string
	 */
	private getDpaVersion(version: number): string {
		const major = version >> 8;
		const minor = version & 0xff;
		return major.toString() + '.' + minor.toString(16).padStart(2, '0');
	}
}
</script>
