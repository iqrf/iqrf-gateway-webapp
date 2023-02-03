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
	<CCard class='border-0 card-margin-bottom'>
		<CCardBody>
			<h4>{{ $t('iqrfnet.networkManager.backupRestore.restore.title') }}</h4><br>
			<ValidationObserver>
				<CForm>
					<div class='form-group'>
						<CInputFile
							ref='backupFile'
							:label='$t("iqrfnet.networkManager.backupRestore.restore.form.backupFile")'
							accept='.iqrfbkp'
							@input='fileInputTouched'
							@click='isEmpty'
						/>
						<em>{{ $t('iqrfnet.networkManager.backupRestore.restore.messages.accessPasswordNote') }}</em>
					</div>
					<CButton
						color='primary'
						:disabled='fileEmpty'
						@click='restoreDevice'
					>
						{{ $t('forms.restore') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardHeader, CCardBody, CForm, CInput, CInputFile, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import {versionHigherEqual} from '@/helpers/versionChecker';

import ini from 'ini';
import IqrfNetService from '@/services/IqrfNetService';

import {IRestoreData} from '@/interfaces/DaemonApi/Iqmesh/Backup';
import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputFile,
		CSelect,
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * IQMESH Restore component card
 */
export default class Restore extends Vue {
	/**
	 * @var {Array<IRestoreData>} restoreData Array of device backup data entries
	 */
	private restoreData: Array<IRestoreData> = [];

	/**
	 * @var {boolean} fileEmpty Is file input empty?
	 */
	private fileEmpty = true;

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null;

	/**
	 * @var {boolean} restartOnRestore Restart coordinator on restore
	 */
	private restartOnRestore = false;

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * @var {boolean} daemon236 Indicates that Daemon version is 2.3.6 or higher
	 */
	private daemon236 = false;

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
			if (mutation.payload.mType === 'iqmeshNetwork_Restore') {
				this.handleRestoreResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'messageError') {
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				this.$toast.error(
					this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
				);
			}
		});
	}

	/**
	 * Checks daemon version for error handling
	 */
	mounted(): void {
		this.daemon236 = versionHigherEqual('2.3.6');
	}

	/**
	 * Recovers from request sent state, hides spinner and removes message id
	 */
	private requestRecovery(): void {
		this.$store.commit('spinner/HIDE');
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
	}

	/**
	 * Restore response message handler
	 * @param data Daemon API response
	 */
	private handleRestoreResponse(data): void {
		this.requestRecovery();
		if (data.status === 0) {
			this.$toast.success(
				this.$t('iqrfnet.networkManager.backupRestore.restore.messages.coordinatorSuccess').toString()
			);
			return;
		}

		if (!this.daemon236 && data.status === 1000) { // error handling before unified codes
			if (data.statusStr.includes('ERROR_TIMEOUT')) {
				this.$toast.error(
					this.$t('forms.messages.coordinatorOffline').toString()
				);
			} else {
				this.$toast.error(
					this.$t('iqrfnet.networkManager.backupRestore.restore.messages.failedMessage', {message: data.statusStr}).toString()
				);
			}
			return;
		}

		if (data.status === -1) { // coordinator device is offline
			this.$toast.error(
				this.$t('forms.messages.coordinatorOffline').toString()
			);
		} else if (data.status === 1004) { // backup data is too long or too short
			this.$toast.error(
				this.$t('iqrfnet.networkManager.backupRestore.restore.messages.invalidSize').toString()
			);
		} else if (data.status === 1005) { // backup data checksum is incorrect
			this.$toast.error(
				this.$t('iqrfnet.networkManager.backupRestore.restore.messages.checksumMismatch').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.backupRestore.restore.messages.failedMessage', {message: data.statusStr}).toString()
			);
		}
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Performs device restoration
	 * @param {number} address device address
	 * @param {string} data
	 */
	private sendRestore(address: number, data: string) {
		this.$store.commit('spinner/SHOW');
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.networkManager.backupRestore.restore.messages.coordinatorRunning').toString()
		);
		const options = new DaemonMessageOptions(null);
		IqrfNetService.restore(address, this.restartOnRestore, data, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Checks for valid combination of data and target device
	 */
	private restoreDevice(): void {
		for (const entry of this.restoreData) {
			if (entry.DataC) {
				this.sendRestore(0, entry.DataC);
				return;
			}
		}
		this.$toast.error(
			this.$t('iqrfnet.networkManager.backupRestore.restore.messages.missingCoordinator').toString()
		);
	}

	/**
	 * Extracts files from file input element
	 */
	private getFiles(): FileList {
		const input = ((this.$refs.backupFile as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Checks if file input element is empty
	 */
	private isEmpty(): void {
		const files = this.getFiles();
		this.fileEmpty = files === null || files.length === 0;
	}

	/**
	 * Clears file input content
	 */
	private clearInput(): void {
		((this.$refs.backupFile as CInputFile).$el.children[1] as HTMLInputElement).value = '';
		this.fileEmpty = true;
		this.$store.commit('spinner/HIDE');
	}

	/**
	 * File input handler
	 */
	private fileInputTouched(): void {
		this.isEmpty();
		if (this.fileEmpty) {
			return;
		}
		this.readContents();
	}

	/**
	 * Attempts to read file content of uploaded file
	 */
	private readContents(): void {
		this.$store.commit('spinner/SHOW');
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.networkManager.backupRestore.restore.messages.parsingContent').toString()
		);
		this.getFiles()[0].text()
			.then((fileContent: string) => {
				this.parseContent(fileContent);
			})
			.catch(() => {
				this.$toast.error(
					this.$t('iqrfnet.networkManager.backupRestore.restore.messages.readFailed').toString()
				);
				this.clearInput();
			});
	}

	/**
	 * Checks if backup data object contains specified property
	 * @param {IRestoreData} obj backup data entry
	 * @param {string} property searched property
	 * @param {string} key backup data entry identifier
	 * @returns {boolean} true if property exists, false otherwise
	 */
	private checkForProp(obj: IRestoreData, property: string, key: string) {
		if (!(property in obj)) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.backupRestore.restore.messages.missingProp', {item: key, property: property}).toString()
			);
			return false;
		}
		return true;
	}

	/**
	 * Parses the content extracted from uploaded file
	 * @param {string} content content of uploaded file
	 */
	private parseContent(content: string): void {
		const restoreData = ini.parse(content);
		if (!('Backup' in restoreData)) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.backupRestore.restore.messages.invalidContent').toString()
			);
			this.clearInput();
			return;
		}
		delete restoreData.Backup;
		const backupKeys = Object.keys(restoreData);
		for (const key of backupKeys) {
			if (!RegExp(/^[0-9A-F]{8}$/i).test(key)) {
				this.$toast.error(
					this.$t('iqrfnet.networkManager.backupRestore.restore.messages.invalidContent').toString()
				);
				this.clearInput();
				return;
			}
			if (!this.validateEntry(restoreData[key], key)) {
				this.clearInput();
				return;
			}
		}
		this.restoreData = Object.keys(restoreData).map(key => restoreData[key]);
		this.$store.commit('spinner/HIDE');
	}

	/**
	 * Validates device backup entry
	 * @param {IRestoreData} entry Device backup entry
	 * @param {string} key Device key (MID hex)
	 */
	private validateEntry(entry: IRestoreData, key: string): boolean {
		if (!this.checkForProp(entry, 'Device', key)) {
			return false;
		}
		if (!this.checkForProp(entry, 'Version', key)) {
			return false;
		}
		if (!this.checkForProp(entry, 'Address', key)) {
			return false;
		}
		const device = entry.Device;
		if (device !== 'Coordinator' && device !== 'Node') { // Check device prop value
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.backupRestore.restore.messages.invalidDevice',
					{entry: key, device: device}
				).toString()
			);
			return false;
		}
		const addr = Number.parseInt(entry.Address);
		if (addr < 0 || addr > 239) { // Check address prop range
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.backupRestore.restore.messages.invalidAddr',
					{entry: key, address: addr}
				).toString()
			);
			return false;
		}
		if (device === 'Coordinator') {
			if (addr !== 0) { // Check invalid coodinator address
				this.$toast.error(
					this.$t(
						'iqrfnet.networkManager.backupRestore.restore.messages.invalidCoordinatorAddr',
						{entry: key, address: addr}
					).toString()
				);
				return false;
			}
			if (!entry.DataC) { // Check for missing coordinator data
				this.$toast.error(
					this.$t(
						'iqrfnet.networkManager.backupRestore.restore.messages.invalidCoordinatorDataC',
						{entry: key}
					).toString()
				);
				return false;
			}
			if (!RegExp(/^[0-9A-F]+$/i).test(entry.DataC)) { // Check for invalid charset
				this.$toast.error(
					this.$t(
						'iqrfnet.networkManager.backupRestore.restore.messages.invalidDataContent',
						{entry: key, device: 'C'}
					).toString()
				);
				return false;
			}
			if (entry.DataN) { // Check for extra node data
				this.$toast.error(
					this.$t(
						'iqrfnet.networkManager.backupRestore.restore.messages.invalidCoordinatorDataN',
						{entry: key}
					).toString()
				);
				return false;
			}
		}
		if (device === 'Node') {
			if (addr === 0) { // Check invalid node address
				this.$toast.error(
					this.$t(
						'iqrfnet.networkManager.backupRestore.restore.messages.invalidNodeAddr',
						{entry: key, address: addr}
					).toString()
				);
				return false;
			}
			if (!entry.DataN) { // Check for missing node data
				this.$toast.error(
					this.$t(
						'iqrfnet.networkManager.backupRestore.restore.messages.invalidNodeDataN',
						{entry: key}
					).toString()
				);
				return false;
			}
			if (!RegExp(/^[0-9A-F]+$/i).test(entry.DataN)) { // Check for invalid charset
				this.$toast.error(
					this.$t(
						'iqrfnet.networkManager.backupRestore.restore.messages.invalidDataContent',
						{entry: key, device: 'N'}
					).toString()
				);
				return false;
			}
			if (entry.DataC) { // Check for extra coordinator data
				this.$toast.error(
					this.$t(
						'iqrfnet.networkManager.backupRestore.restore.messages.invalidNodeDataC',
						{entry: key}
					).toString()
				);
			}
		}
		return true;
	}
}
</script>
