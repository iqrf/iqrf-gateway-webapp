<template>
	<CCard class='border-0 card-margin-bottom'>
		<CCardBody>
			<h4>{{ $t('iqrfnet.networkManager.restore.title') }}</h4><br>
			<ValidationObserver>
				<CForm @submit.prevent='restoreDevice'>
					<div class='form-group'>
						<CInputFile
							ref='backupFile'
							:label='$t("iqrfnet.networkManager.restore.form.backupFile")'
							@input='fileInputTouched'
							@click='isEmpty'
						/>
						<p v-if='fileEmpty && !fileUntouched' style='color:red'>
							{{ $t('iqrfnet.networkManager.restore.form.messages.backupFile') }}
						</p>
					</div>			
					<CButton
						type='submit'
						color='primary'
						:disabled='fileEmpty'
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
import {versionHigherEqual} from '../../helpers/versionChecker';

import ini from '../../../node_modules/ini';
import IqrfNetService from '../../services/IqrfNetService';

import {IRestoreData} from '../../interfaces/iqmeshServices';
import {MutationPayload} from 'vuex';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';

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
	private restoreData: Array<IRestoreData> = []

	/**
	 * @var {boolean} fileUntouched Has file input been interacted with?
	 */
	private fileUntouched = true
	
	/**
	 * @var {boolean} fileEmpty Is file input empty?
	 */
	private fileEmpty = true

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {boolean} restartOnRestore Restart coordinator on restore
	 */
	private restartOnRestore = false

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * @var {boolean} daemon236 Indicates that Daemon version is 2.3.6 or higher
	 */
	private daemon236 = false

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONERROR' ||
				mutation.type === 'SOCKET_ONCLOSE') {
				if (this.$store.getters['spinner/isEnabled']) {
					this.$store.commit('spinner/HIDE');
				}
				return;
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			if (mutation.payload.mType === 'iqmeshNetwork_Restore') {
				this.handleRestoreResponse(mutation.payload.data);
			} else if (mutation.payload.mType === 'messageError') {
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('removeMessage', this.msgId);
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
		this.$store.dispatch('removeMessage', this.msgId);
	}

	/**
	 * Restore response message handler
	 * @param {any} data Daemon API response
	 */
	private handleRestoreResponse(data: any): void {
		this.requestRecovery();
		if (data.status === 0) {
			this.$toast.success(
				this.$t('iqrfnet.networkManager.restore.messages.coordinatorSuccess').toString()
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
					this.$t('iqrfnet.networkManager.restore.messages.failedMessage', {message: data.statusStr}).toString()
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
				this.$t('iqrfnet.networkManager.restore.messages.invalidSize').toString()
			);
		} else if (data.status === 1005) { // backup data checksum is incorrect
			this.$toast.error(
				this.$t('iqrfnet.networkManager.restore.messages.checksumMismatch').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.restore.messages.failedMessage', {message: data.statusStr}).toString()
			);
		}
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
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
			this.$t('iqrfnet.networkManager.restore.messages.coordinatorRunning').toString()
		);
		const options = new WebSocketOptions(null);
		IqrfNetService.restore(address, this.restartOnRestore, data, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Checks for valid combination of data and target device
	 */
	private restoreDevice(): void {
		for (let entry of this.restoreData) {
			if (entry.DataC) {
				this.sendRestore(0, entry.DataC);
				return;
			}
		}	
		this.$toast.error(
			this.$t('iqrfnet.networkManager.restore.messages.missingCoordinator').toString()
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
		if (this.fileUntouched) {
			this.fileUntouched = false;
		}
		const files = this.getFiles();
		this.fileEmpty = files === null || files.length === 0;
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
		this.getFiles()[0].text()
			.then((fileContent: string) => {
				this.parseContent(fileContent);
			})
			.catch(() => this.$toast.error(
				this.$t('iqrfnet.networkManager.restore.messages.corruptedFile').toString()
			));
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
				this.$t('iqrfnet.networkManager.restore.messages.missingProp', {item: key, property: property}).toString()
			);
			this.fileEmpty = true;
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
				this.$t('iqrfnet.networkManager.restore.messages.invalidContent').toString()
			);
			this.fileEmpty = true;
		}
		delete restoreData.Backup;
		const backupKeys = Object.keys(restoreData);
		for (let key of backupKeys) {
			if (!RegExp('^[0-9A-F]{8}$').test(key)) {
				this.$toast.error(
					this.$t('iqrfnet.networkManager.restore.messages.invalidContent').toString()
				);
				this.fileEmpty = true;
			}
			if (!this.checkForProp(restoreData[key], 'Device', key) ||
				!this.checkForProp(restoreData[key], 'Version', key) ||
				!this.checkForProp(restoreData[key], 'Address', key)) {
				this.fileEmpty = true;
				return;
			}
			if (Number.parseInt(restoreData[key]['Address']) === 0 && !('DataC' in restoreData[key])) {
				this.$toast.error(
					this.$t('iqrfnet.networkManager.restore.messages.missingProp', {item: key, property: 'DataC'}).toString()
				);
				this.fileEmpty = true;
				return;
			} else if (Number.parseInt(restoreData[key]['Address']) > 0 && !('DataN' in restoreData[key])) {
				this.$toast.error(
					this.$t('iqrfnet.networkManager.restore.messages.missingProp', {item: key, property: 'DataN'}).toString()
				);
				this.fileEmpty = true;
				return;
			}
		}
		this.restoreData = Object.keys(restoreData).map(key => restoreData[key]);
	}
}
</script>
