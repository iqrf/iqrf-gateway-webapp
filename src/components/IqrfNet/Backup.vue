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
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';
import IqrfNetService from '../../services/IqrfNetService';
import { MutationPayload } from 'vuex';
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
	 * @var {string} target backup target type
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
				switch(mutation.payload.data.status) {
					case -1:
						this.$toast.error(
							this.$t('iqrfnet.networkManager.backup.messages.timeout').toString()
						);
						break;
					default:
						this.$store.commit('spinner/UPDATE_TEXT', this.backupProgress(mutation.payload.data));
						if (mutation.payload.data.status === 0) {
							this.deviceData.push(mutation.payload.data.rsp.devices[0]);
						}
						if (mutation.payload.data.rsp.progress === 100) {
							this.$store.commit('spinner/HIDE');
							this.$store.dispatch('removeMessage', this.msgId);
							this.generateBackupFile();
							this.$toast.success(
								this.$t('iqrfnet.networkManager.backup.messages.success').toString()
							);
						}
						break;
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

	/**
	 * Performs device backup via daemon API
	 */
	private backupDevice(): void {
		this.deviceData = [];
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
			message += '\n' + this.$t('iqrfnet.networkManager.backup.messages.statusSuccess', {deviceAdddr: response.rsp.devices[0].deviceAddr}).toString();
		} else if (response.status === 1000) {
			message += '\n' + this.$t('iqrfnet.networkManager.backup.messages.statusFailed', {deviceAdddr: response.rsp.devices[0].deviceAddr}).toString();
		}
		return message;
	}
	
	/**
	 * Generates backup file and prompts file save
	 */
	private generateBackupFile(): void {
		let fileContent = '[Backup]\nCreated=' + new Date().toLocaleString().replace(/\//g, ' ') + '\n\n';
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
