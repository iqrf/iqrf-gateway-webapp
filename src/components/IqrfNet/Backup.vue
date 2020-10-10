<template>
	<CCard class='border-0'>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm>
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
						color='primary'
						:disabled='invalid'
						@click='backupDevice'
					>
						{{ $t('forms.backup') }}
					</CButton>
					<CButton 
						v-if='deviceData.length > 0'
						color='secondary'
						@click='downloadFiles'
					>
						{{ $t('forms.downloadBackup') }}
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
import JSZip from 'jszip';
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
							const device = mutation.payload.data.rsp.devices[0];
							this.deviceData.push({deviceAddr: device.deviceAddr, data: device.data});
						}
						if (mutation.payload.data.rsp.progress === 100) {
							this.$store.commit('spinner/HIDE');
							this.$store.dispatch('removeMessage', this.msgId);
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
		downloadFiles(): void {
			const zip = new JSZip();
			this.deviceData.forEach(element => {
				zip.file('backup_device' + element.deviceAddr, element.data);
			});
			zip.generateAsync({type: 'blob'})
				.then((blob) => {
					saveAs(blob, 'iqmeshBackup_' + new Date().toISOString().replace(' ', '_').replace('.', '_'));
				});
		}
	}
});
</script>

<style scoped>
.btn {
  margin: 0 3px 0 0;
}
</style>

