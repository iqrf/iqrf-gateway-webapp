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
	<CModal
		:show.sync='show'
		color='danger'
		size='lg'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('config.daemon.messagings.websocket.removeDialog.interfaceTitle') }}
			</h5>
		</template>
		{{ $t('config.daemon.messagings.websocket.removeDialog.interfacePrompt', {instance: messagingInstance}) }}
		<template #footer>
			<CButton
				class='mr-1'
				color='secondary'
				@click='hideModal'
			>
				{{ $t('forms.cancel') }}
			</CButton>
			<CButton
				color='danger'
				@click='remove'
			>
				{{ $t('forms.delete') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';

/**
 * Websocket interface delete dialog component
 */
@Component({
	components: {
		CButton,
		CModal,
	},
})
export default class WebsocketInterfaceDeleteModal extends ModalBase {
	/**
	 * @constant {string} messagingComponent Websocket messaging component
	 */
	private readonly messagingComponent = 'iqrf::WebsocketMessaging';

	/**
	 * @constant {string} serviceComponent Websocket service component
	 */
	private readonly serviceComponent = 'shape::WebsocketCppService';

	/**
	 * @var {string} messagingInstance Websocket messaging instance
	 */
	private messagingInstance = '';

	/**
	 * @var {string} serviceInstance Websocket service instance
	 */
	private serviceInstance = '';

	/**
	 * Removes an existing instance of Websocket interface component
	 */
	private remove(): void {
		this.closeModal();
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.messagingComponent, this.messagingInstance),
			DaemonConfigurationService.deleteInstance(this.serviceComponent, this.serviceInstance),
		])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'config.daemon.messagings.websocket.removeDialog.interfaceDeleteSuccess',
						{instance: this.messagingInstance},
					).toString()
				);
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.messagings.websocket.removeDialog.interfaceDeleteFailed',
					{interface: this.messagingInstance},
				);
			});
	}

	/**
	 * Stores messaging and service instances, and shows modal window
	 * @param {string} messaging Messaging instance
	 * @param {string} service Service instance
	 */
	public showModal(messaging: string, service: string): void {
		this.messagingInstance = messaging;
		this.serviceInstance = service;
		this.openModal();
	}

	/**
	 * Resets messaging and service instances, and hides modal window
	 */
	private hideModal(): void {
		this.messagingInstance = '';
		this.serviceInstance = '';
		this.closeModal();
	}
}

</script>
