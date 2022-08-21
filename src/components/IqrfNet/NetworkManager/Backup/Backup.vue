<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<v-card flat tile>
			<v-card-title>{{ $t('iqrfnet.networkManager.backupRestore.backup.title') }}</v-card-title>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form @submit.prevent='backupDevice'>
						<v-select
							v-model='target'
							:label='$t("iqrfnet.networkManager.backupRestore.backup.form.target")'
							:items='targets'
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
		<BackupProgressDialog
			ref='result'
			:target='target'
			:progress='progress'
			:devices='deviceData'
			:webapp-version='webappVersion'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import BackupProgressDialog from './BackupProgressDialog.vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import {NetworkTarget} from '@/enums/IqrfNet/network';


import IqrfNetService from '@/services/IqrfNetService';
import VersionService from '@/services/VersionService';

import {AxiosResponse} from 'axios';
import {IBackupData} from '@/interfaces/iqmeshServices';
import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import {IOption} from '@/interfaces/coreui';

@Component({
	components: {
		BackupProgressDialog,
		ValidationObserver,
		ValidationProvider
	},
})

/**
 * IQMESH Backup component card
 */
export default class Backup extends Vue {
	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {number} address Address of device to backup
	 */
	private address = 1;

	/**
	 * @var {number} progress Backu progress
	 */
	private progress = 0;

	/**
	 * @var {Array<IBackupData>} deviceData Array of device backup data
	 */
	private deviceData: Array<IBackupData> = [];

	/**
	 * @var {Array<number>} offlineDevices Array of devices offline during the backup process
	 */
	private offlineDevices: Array<number> = [];

	/**
	 * @var {string} target Backup target type
	 */
	private target = NetworkTarget.COORDINATOR;

	/**
	 * @var {Array<unknown>} selectOptions CoreUI form select options
	 */
	private targets: Array<IOption> = [
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
		VersionService.getWebappVersionRest()
			.then((response: AxiosResponse) => this.webappVersion = response.data.version)
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
	 * Performs device backup via daemon API
	 */
	private backupDevice(): void {
		this.progress = 0;
		this.deviceData = [];
		this.offlineDevices = [];
		const address = this.target === NetworkTarget.NODE ? this.address : 0;
		const wholeNetwork = this.target === NetworkTarget.NETWORK;
		const options = new DaemonMessageOptions(null);
		IqrfNetService.backup(address, wholeNetwork, options)
			.then((msgId: string) => this.msgId = msgId);
		(this.$refs.result as BackupProgressDialog).showDialog();
	}

	/**
	 * Backup response message handler
	 * @param data Daemon API response
	 */
	private handleBackupResponse(data): void {
		if (data.status >= 1000) {
			(this.$refs.result as BackupProgressDialog).setError(data.statusStr);
			return;
		}
		const backupData: IBackupData = data.rsp.devices[0];
		this.progress = data.rsp.progress;
		this.deviceData.push(backupData);
	}
}
</script>
