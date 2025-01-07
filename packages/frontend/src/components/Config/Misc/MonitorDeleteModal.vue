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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>
				<h5>{{ $t('config.daemon.misc.monitor.modal.title') }}</h5>
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.misc.monitor.modal.prompt', {instance: _monitorInstance}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='remove'
				>
					{{ $t('forms.delete') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, VModel, PropSync, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';

/**
 * Monitor delete modal component
 */
@Component
export default class MonitorDeleteModal extends Vue {

	/**
	 * @property {boolean} show Controls modal visilibity
	 */
	@VModel({required: true}) show!: boolean;

	/**
	 * @property {string} _monitorInstance Monitor instance to delete
	 */
	@PropSync('monitorInstance', {required: true, default: ''}) _monitorInstance!: string;

	/**
	 * @property {string} _websocketInstance Websocket instance to delete
	 */
	@PropSync('websocketInstance', {required: true, default: ''}) _websocketInstance!: string;

	/**
	 * @constant {string} monitorComponent Monitor component
	 */
	private readonly monitorComponent = 'iqrf::MonitorService';

	/**
	 * @constant {string} websocketComponent Websocket component
	 */
	private readonly websocketComponent = 'shape::WebsocketCppService';

	/**
	 * Removes instance of the monitoring component
	 */
	private remove(): void {
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.monitorComponent, this._monitorInstance),
			DaemonConfigurationService.deleteInstance(this.websocketComponent, this._websocketInstance),
		])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'config.daemon.misc.monitor.messages.deleteSuccess',
						{instance: this._monitorInstance}
					).toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.misc.monitor.messages.deleteFailed',
					{instance: this._monitorInstance},
				);
			});
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this._monitorInstance = '';
		this._websocketInstance = '';
		this.show = false;
	}
}
</script>
