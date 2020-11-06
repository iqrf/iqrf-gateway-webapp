<template>
	<CCard class='border-top-0 border-left-0 border-right-0'>
		<CCardHeader>
			{{ $t('iqrfnet.networkManager.backup.title') }}
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='backupDevice'>
					<CSelect
						:value.sync='target'
						:options='selectOptions'
						:placeholder='$t("iqrfnet.networkManager.backup.form.messages.select")'
					/>
					<ValidationProvider
						v-if='target === "node"'
						v-slot='{errors, touched, valid}'
						rules='required|integer|between:1,239'
						:custom-messages='{
							integer: "iqrfnet.networkManager.backup.form.messages.address",
							between: "iqrfnet.networkManager.backup.form.messages.address",
							required: "iqrfnet.networkManager.backup.form.messages.address"
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("iqrfnet.networkManager.backup.form.address")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CButton
						type='submit'
						color='primary'
						:disabled='invalid'
					>
						{{ $t('forms.backup') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';
import IqrfNetService from '../../services/IqrfNetService';
import {MutationPayload} from 'vuex';
import {saveAs} from 'file-saver';

interface DeviceData {
	data: string
	deviceAddr: number
	dpaVer: number
	mid: number
	online: boolean
}

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * IQMESH Backup component card
 */
export default class Backup extends Vue {
	/**
	 * @var {number} address Address of device to backup
	 */
	private address = 1

	/**
	 * @var {Array<DeviceData>} deviceData Array of device backup data
	 */
	private deviceData: Array<DeviceData> = []

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {Array<number>} offlineDevices Array of devices offline during the backup process
	 */
	private offlineDevices: Array<number> = []

	/**
	 * @var {Array<unknown>} selectOptions CoreUI form select options
	 */
	private selectOptions: Array<unknown> = [
		{
			value: 'coordinator',
			label: this.$t('iqrfnet.networkManager.backup.form.coordinator'),
		},
		{
			value: 'node',
			label: this.$t('iqrfnet.networkManager.backup.form.node'),
		},
		{
			value: 'network',
			label: this.$t('iqrfnet.networkManager.backup.form.network'),
		}
	]

	/**
	 * @var {string} target Backup target type
	 */
	private target = ''

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

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
			if (mutation.payload.data.msgId === this.msgId) {
				if (mutation.payload.mType === 'iqmeshNetwork_Backup') {
					this.handleBackupResponse(mutation.payload.data);
				}
			}
		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	}

	private concludeBackup() {
		this.$store.commit('spinner/HIDE');
		this.$store.dispatch('removeMessage', this.msgId);
		this.generateBackupFile();
		this.backupSuccessToast();
	}

	private backupSuccessToast(): void {
		if (this.offlineDevices.length === 0) {
			this.$toast.success(
				this.$t('iqrfnet.networkManager.backup.messages.success').toString()
			);
		} else {
			this.$toast.info(
				this.$t('iqrfnet.networkManager.backup.messages.partialSuccess', {devices: this.offlineDevices.join(', ')}).toString()
			);
		}
	}

	private handleBackupResponse(data: any): void {
		if (data.status === 0) { // no error detected
			this.$store.commit('spinner/UPDATE_TEXT', this.backupProgress(data));
			this.deviceData.push(data.rsp.devices[0]);
			if (data.rsp.progress !== 100) { // backup process not finished
				return;
			}
			this.concludeBackup();
			return;
		}

		if (data.status === -1) { // request timed out
			this.$store.commit('spinner/HIDE');
			this.$store.dispatch('removeMessage', this.msgId);
			this.$toast.error(
				this.$t('iqrfnet.networkManager.backup.messages.timeout').toString()
			);
			return;
		}

		if (data.status !== 1000) { // is not a backup response code
			return;
		}

		if (this.target === 'coordinator' && data.rsp.progress === 100) {
			this.$store.commit('spinner/HIDE');
			this.$store.dispatch('removeMessage', this.msgId);
			this.$toast.error(
				this.$t('iqrfnet.networkManager.backup.messages.nodeOffline', {address: 0}).toString()
			);
			return;
		}

		if (data.statusStr.includes('ERROR_NADR')) { // device not bonded
			if (this.target === 'node' && data.rsp.progress === 100) {
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('removeMessage', this.msgId);
				this.$toast.error(
					this.$t('iqrfnet.networkManager.backup.messages.invalidNadr', {address: this.address}).toString()
				);
				return;
			}
			if (this.target === 'network' && data.rsp.progress === 100) { // device in coordinator address but not found
				this.concludeBackup();
				return;
			}
		}

		if (data.statusStr.includes('ERROR_TIMEOUT')) { // device is offline
			if (this.target === 'node' && data.rsp.progress === 100) {
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('removeMessage', this.msgId);
				this.$toast.error(
					this.$t('iqrfnet.networkManager.backup.messages.nodeOffline', {address: this.address}).toString()
				);
				return;
			}
			if (this.target === 'network' && data.rsp.progress === 100) { // backup finished, but last node device was offline
				this.offlineDevices.push(data.rsp.devices[0].deviceAddr);
				this.concludeBackup();
				return;
			}
			this.offlineDevices.push(data.rsp.devices[0].deviceAddr);
			this.$store.commit('spinner/UPDATE_TEXT', this.backupProgress(data));
		}
	}

	/**
	 * Performs device backup via daemon API
	 */
	private backupDevice(): void {
		this.deviceData = [];
		this.offlineDevices = [];
		const address = this.target === 'node' ? this.address : 0;
		const wholeNetwork = this.target === 'network';
		const options = new WebSocketOptions(null);
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', '\n' + this.$t('iqrfnet.networkManager.backup.messages.statusInit').toString());
		IqrfNetService.backup(address, wholeNetwork, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Creates backup progress message for spinner
	 * @param response daemon api response
	 * @returns {string} Backup progress message
	 */
	private backupProgress(response: any): string {
		let message = '\n' + this.$t('iqrfnet.networkManager.backup.messages.statusRunning', {progress: response.rsp.progress}).toString();
		if (response.status === 0) {
			message += '\n' + this.$t('iqrfnet.networkManager.backup.messages.statusSuccess', {deviceAddr: response.rsp.devices[0].deviceAddr}).toString();
		} else if (response.status === 1000) {
			message += '\n' + this.$t('iqrfnet.networkManager.backup.messages.statusFailed', {deviceAddr: response.rsp.devices[0].deviceAddr}).toString();
		}
		return message;
	}
	
	/**
	 * Generates backup file and prompts file save
	 */
	private generateBackupFile(): void {
		let fileContent = '[Backup]\nCreated=' + new Date().toLocaleString('en-GB').replace(/\//g, '.').replace(/,/g, '') + '\n\n';
		let fileName = '';
		if (this.target === 'coordinator') {
			fileName = 'Coordinator_';
			fileContent += this.coordinatorBackup() + '\n';
		} else if (this.target === 'node') {
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
