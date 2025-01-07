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
				<h5>{{ $t('config.daemon.messagings.websocket.removeDialog.interfaceTitle') }}</h5>
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.messagings.websocket.removeDialog.interfacePrompt', {instance: _messagingInstance}) }}
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
import {Component, PropSync, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';

/**
 * Websocket interface delete dialog component
 */
@Component
export default class WebsocketInterfaceDeleteModal extends Vue {

	/**
	 * @property {boolean} show Controls modal visibility
	 */
	@VModel({required: true}) show!: boolean;

	/**
	 * @property {string} _messagingInstance Messaging service instance
	 */
	@PropSync('messagingInstance', {required: true, default: ''}) _messagingInstance!: string;

	/**
	 * @property {string} __serviceInstance Websocket service instance
	 */
	@PropSync('serviceInstance', {required: true, default: ''}) _serviceInstance!: string;

	/**
	 * @constant {string} messagingComponent Websocket messaging component
	 */
	private readonly messagingComponent = 'iqrf::WebsocketMessaging';

	/**
	 * @constant {string} serviceComponent Websocket service component
	 */
	private readonly serviceComponent = 'shape::WebsocketCppService';

	/**
	 * Removes an existing instance of Websocket interface component
	 */
	private remove(): void {
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.messagingComponent, this._messagingInstance),
			DaemonConfigurationService.deleteInstance(this.serviceComponent, this._serviceInstance),
		])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'config.daemon.messagings.websocket.removeDialog.interfaceDeleteSuccess',
						{instance: this._messagingInstance},
					).toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.messagings.websocket.removeDialog.interfaceDeleteFailed',
					{interface: this._messagingInstance},
				);
			});
	}

	/**
	 * Resets messaging and service instances, and hides modal window
	 */
	private hideModal(): void {
		this._messagingInstance = '';
		this._serviceInstance = '';
		this.show = false;
	}
}

</script>
