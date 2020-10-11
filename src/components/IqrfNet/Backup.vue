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
import Vue from 'vue';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';
import IqrfNetService from '../../services/IqrfNetService';
import { MutationPayload } from 'vuex';
import {saveAs} from 'file-saver';

export default Vue.extend({
	name: 'Backup',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	data(): any {
		return {
			address: 1,
			selectOptions: [
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
			],
			target: null,
			msgId: null,
			deviceData: [],
		};
	},
	created() {
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
							this.$t('iqrfnet.networkManager.backup.messages.timeoout').toString()
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
	},
	beforeDestroy() {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	},
	methods: {
		backupDevice(): void {
			this.deviceData = [];
			const address = this.target === 'node' ? this.address : 0;
			const wholeNetwork = this.target === 'network';
			const options = new WebSocketOptions(null);
			this.$store.commit('spinner/SHOW');
			this.$store.commit('spinner/UPDATE_TEXT', '\nBackup running [0 %]');
			IqrfNetService.backup(address, wholeNetwork, options)
				.then((msgId: string) => this.msgId = msgId);
		},
		backupProgress(response: any): string {
			let message = '\nBackup running [' + response.rsp.progress + ' %]';
			if (response.status === 0) {
				message += '\nBackup of device ' + response.rsp.devices[0].deviceAddr + ' completed.';
			} else if (response.status === 1000) {
				message += '\nBackup of device ' + response.rsp.devices[0].deviceAddr + ' failed: Device is offline.';
			}
			return message;
		},
		generateBackupFile(): void {
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
			fileName += this.deviceData[0].mid.toString(16) + '_' + new Date().toISOString().slice(2, 10).replace(/-/g, '') + '.iqrfbkp';
			const blob = new Blob([fileContent], {type: 'text/plain;charset=utf-8'});
			saveAs(blob, fileName);
		},
		coordinatorBackup(): string {
			const device = this.deviceData[0];
			let message = '[' + device.mid.toString(16) + ']\n';
			message += 'Device=Coordinator\nVersion=' + this.getDpaVersion(device.dpaVer) + '\n';
			message += 'DataC=' + device.data + '\nAddress=' + device.deviceAddr + '\n';
			return message;
		},
		nodeBackup(index: number): string {
			const device = this.deviceData[index];
			let message = '[' + device.mid.toString(16) + ']\n';
			message += 'Device=Node\nVersion=' + this.getDpaVersion(device.dpaVer) + '\n';
			message += 'DataN=' + device.data + '\nAddress=' + device.deviceAddr + '\n';
			return message;
		},
		networkBackup(): string {
			let message = this.coordinatorBackup() + '\n';
			for (let i = 1; i < this.deviceData.length; ++i) {
				message += this.nodeBackup(i) + '\n';
			}
			return message;
		},
		getDpaVersion(version: number): string {
			const major = version >> 8;
			const minor = version & 0xff;
			return major.toString() + '.' + minor.toString(16).padStart(2, '0');
		}
	}
});
</script>

<style scoped>
.btn {
  margin: 0 3px 0 0;
}
</style>

